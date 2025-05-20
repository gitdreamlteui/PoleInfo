#include "dialog.h"
#include "ui_dialog.h"
#include <QMessageBox>
#include <QDebug>
#include <QTimer>
#include <QNetworkAccessManager>
#include <QNetworkReply>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonArray>
#include <QTextCodec>
#include <QThread>
#include <algorithm> // Pour std::sort
#include <QFile> // Pour lire le fichier JSON
#include <QCoreApplication> // Pour applicationDirPath()

Dialog::Dialog(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::Dialog),
    serialPort(new QSerialPort(this)),
    manager(new QNetworkAccessManager(this)),
    timer(new QTimer(this)),
    lastResponse("")
{
    ui->setupUi(this);
    setWindowTitle("Afficheur LED Mc Crypt 590996");

    // Charger la configuration initiale depuis le fichier JSON
    if (!loadConfig()) {
        qDebug() << "Échec du chargement initial de config.json";
        QMessageBox::critical(this, "Erreur", "Impossible de charger config.json. L'application va se fermer.");
        QCoreApplication::quit(); // Quitter l'application si config.json est manquant ou invalide
        return;
    }

    connect(ui->pushButtonSend, &QPushButton::clicked, this, &Dialog::onSendButtonClicked);
    connect(serialPort, &QSerialPort::readyRead, this, &Dialog::receiveData);
    connect(timer, &QTimer::timeout, this, &Dialog::updateDisplay);
    connect(ui->eraseAll, &QPushButton::clicked, this, &Dialog::on_eraseAll_clicked);

    if (!openSerialPort()) {
        QMessageBox::critical(this, "Erreur", "Échec de l'initialisation du port série.");
    }

    timer->start(60000);
    updateDisplay();
}

Dialog::~Dialog()
{
    closeSerialPort();
    delete ui;
}

bool Dialog::loadConfig()
{
    // Chercher config.json dans le dossier de l'exécutable
    QString configPath = QCoreApplication::applicationDirPath() + "/config.json";
    QFile configFile(configPath);
    if (!configFile.open(QIODevice::ReadOnly | QIODevice::Text)) {
        qDebug() << "Erreur : impossible d'ouvrir config.json à" << configPath;
        return false; // Pas de valeurs par défaut
    }

    QByteArray configData = configFile.readAll();
    QJsonDocument jsonDoc = QJsonDocument::fromJson(configData);
    if (jsonDoc.isNull() || !jsonDoc.isObject()) {
        qDebug() << "Erreur : format JSON invalide dans config.json";
        configFile.close();
        return false; // Pas de valeurs par défaut
    }

    QJsonObject configObj = jsonDoc.object();
    if (!configObj.contains("port") || !configObj.contains("salle")) {
        qDebug() << "Erreur : config.json doit contenir les clés 'port' et 'salle'";
        configFile.close();
        return false; // Pas de valeurs par défaut
    }

    portName = configObj.value("port").toString();
    salle = configObj.value("salle").toString();

    if (portName.isEmpty() || salle.isEmpty()) {
        qDebug() << "Erreur : les valeurs 'port' ou 'salle' dans config.json sont vides";
        configFile.close();
        return false; // Pas de valeurs par défaut
    }

    qDebug() << "Configuration chargée : port =" << portName << ", salle =" << salle;
    configFile.close();
    return true;
}

bool Dialog::openSerialPort()
{
    serialPort->setPortName(portName); // Utiliser portName chargé depuis config.json
    serialPort->setBaudRate(QSerialPort::Baud9600);
    serialPort->setDataBits(QSerialPort::Data8);
    serialPort->setParity(QSerialPort::NoParity);
    serialPort->setStopBits(QSerialPort::OneStop);
    serialPort->setFlowControl(QSerialPort::NoFlowControl);

    if (serialPort->open(QIODevice::ReadWrite)) {
        qDebug() << "Port série ouvert avec succès :" << portName;
        return true;
    } else {
        QString errorMsg = "Impossible d'ouvrir le port série : " + serialPort->errorString();
        if (serialPort->error() == QSerialPort::PermissionError) {
            errorMsg += "\nVérifiez les permissions sur " + portName + ". Essayez 'sudo adduser $USER dialout' et redémarrez.";
        }
        qDebug() << errorMsg;
        return false;
    }
}

void Dialog::closeSerialPort()
{
    if (serialPort->isOpen()) {
        serialPort->close();
        qDebug() << "Port série fermé.";
    }
}

bool Dialog::sendData(const QByteArray &data)
{
    if (!serialPort->isOpen()) {
        qDebug() << "Port série non ouvert.";
        return false;
    }

    qDebug() << "État du port avant envoi :" << (serialPort->isOpen() ? "Ouvert" : "Fermé");
    qint64 bytesWritten = serialPort->write(data);
    serialPort->waitForBytesWritten(1000);
    if (bytesWritten != data.size()) {
        qDebug() << "Erreur lors de l'envoi :" << serialPort->errorString();
        return false;
    }

    qDebug() << "Trame envoyée :" << data.toHex(' ');
    lastResponse.clear();
    int timeout = 5000; // 5 secondes
    while (timeout > 0) {
        if (serialPort->waitForReadyRead(100)) {
            lastResponse += serialPort->readAll();
            qDebug() << "Fragment reçu :" << lastResponse.toHex(' ');
            if (lastResponse.contains("ACK")) {
                qDebug() << "Réponse complète : ACK";
                return true;
            } else if (lastResponse.contains("NACK")) {
                qDebug() << "Réponse complète : NACK";
                return false;
            } else if (lastResponse.contains("NACKz")) {
                qDebug() << "Réponse complète : NACK avec checksum attendu";
                return false;
            }
        }
        timeout -= 100;
        QThread::msleep(10);
    }

    qDebug() << "Erreur : réponse incomplète ou absente :" << lastResponse.toHex(' ');
    return false;
}

unsigned char Dialog::calculateChecksum(const QByteArray &data)
{
    unsigned char checksum = 0;
    for (int i = 0; i < data.size(); ++i) {
        checksum ^= static_cast<unsigned char>(data[i]);
        qDebug() << "Caractère" << i << ":" << QString("%1").arg(data[i], 2, 16, QChar('0')).toUpper() << ", Checksum intermédiaire :" << QString("%1").arg(checksum, 2, 16, QChar('0')).toUpper();
    }
    qDebug() << "Checksum final pour" << data << ":" << QString("%1").arg(checksum, 2, 16, QChar('0')).toUpper();
    return checksum;
}

bool Dialog::sendClockUpdate()
{
    if (!serialPort->isOpen()) {
        qDebug() << "Port série non ouvert pour le réglage de l'horloge, tentative de réouverture.";
        if (!openSerialPort()) {
            qDebug() << "Échec de l'ouverture du port série pour le réglage de l'horloge.";
            return false;
        }
    }

    // Récupérer la date et l'heure actuelles
    QDateTime now = QDateTime::currentDateTime();
    QString year = QString("%1").arg(now.date().year() % 100, 2, 10, QChar('0')); // 2 derniers chiffres de l'année
    QString dayOfWeek = QString("%1").arg(now.date().dayOfWeek(), 2, 10, QChar('0')); // 01 (lundi) à 07 (dimanche)
    QString month = QString("%1").arg(now.date().month(), 2, 10, QChar('0')); // 01 à 12
    QString day = QString("%1").arg(now.date().day(), 2, 10, QChar('0')); // 01 à 31
    QString hour = QString("%1").arg(now.time().hour(), 2, 10, QChar('0')); // 00 à 23
    QString minute = QString("%1").arg(now.time().minute(), 2, 10, QChar('0')); // 00 à 59
    QString second = QString("%1").arg(now.time().second(), 2, 10, QChar('0')); // 00 à 59

    // Construire la partie données de la trame
    QByteArray clockData = QString("<ID01><SC>%1%2%3%4%5%6%7")
                           .arg(year, dayOfWeek, month, day, hour, minute, second)
                           .toUtf8();

    // Calculer la somme de contrôle (sur la partie données, à partir de <SC>...)
    unsigned char checksum = calculateChecksum(clockData.mid(6));
    clockData.append(QString("%1").arg(checksum, 2, 16, QChar('0')).toUpper());
    clockData.append("<E>");

    qDebug() << "Trame de réglage de l'horloge :" << clockData;

    // Envoyer la trame
    if (!sendData(clockData)) {
        qDebug() << "Échec de l'envoi de la trame de réglage de l'horloge.";
        return false;
    }

    QThread::msleep(500); // Attendre 500ms pour éviter les collisions avec d'autres trames
    return true;
}

void Dialog::onSendButtonClicked()
{
    updateDisplay();
}

void Dialog::receiveData()
{
    QByteArray response = serialPort->readAll();
    if (!response.isEmpty()) {
        lastResponse += response;
        qDebug() << "Réponse reçue (asynchrone) :" << response.toHex(' ');
    }
}

void Dialog::sendNextPage(const QMap<QString, QString> &pagesMessages)
{
    if (!serialPort->isOpen()) {
        qDebug() << "Port série non ouvert, tentative de réouverture.";
        if (!openSerialPort()) return;
    }

    // Vérifier si c'est le cas "Salle libre"
    bool isSalleLibre = (pagesMessages["A"] == "Salle libre" &&
                        pagesMessages["B"] == "Salle libre" &&
                        pagesMessages["C"] == "Salle libre");

    // Étape 1 : Effacer les pages A, B et C
    for (const QString &page : pagesMessages.keys()) {
        QByteArray clearPageData = QString("<ID01><DL1P%1>").arg(page).toUtf8();
        unsigned char clearChecksum = calculateChecksum(clearPageData);
        clearPageData.append(QString("%1").arg(clearChecksum, 2, 16, QChar('0')).toUpper());
        clearPageData.append("<E>");
        qDebug() << "Trame d'effacement pour page" << page << ":" << clearPageData;
        if (!sendData(clearPageData)) {
            qDebug() << "Échec de l'effacement de la page" << page;
            return;
        }
        QThread::msleep(500);
    }

    // Étape 2 : Configurer les pages
    if (isSalleLibre) {
        // Cas spécial "Salle libre" : clignotage rouge pour toutes les pages
        for (const QString &page : pagesMessages.keys()) {
            QString message = "Salle libre";
            int messageLength = message.length() * 6; // 6 points par caractère (police normale)
            int startPosition = (80 - messageLength) / 2; // Centrer sur 80 colonnes
            if (startPosition < 0) startPosition = 0;
            QString positionHex = QString("%1").arg(startPosition, 2, 16, QChar('0')).toUpper();
            QByteArray pageData = QString("<ID01><L1><P%1><FA><MB><WC><FA><CB><N%2>%3")
                                  .arg(page).arg(positionHex).arg(message).toUtf8();
            unsigned char checksum = calculateChecksum(pageData.mid(6));
            pageData.append(QString("%1").arg(checksum, 2, 16, QChar('0')).toUpper());
            pageData.append("<E>");
            qDebug() << "Trame pour page" << page << "(Salle libre, clignotage rouge) :" << pageData;
            if (!sendData(pageData)) {
                qDebug() << "Échec de l'envoi de la page" << page;
                return;
            }
            QThread::msleep(500);
        }
    } else {
        // Cas normal (réservation en cours ou future)
        // Page A : heure centrée, rouge clignotante, 2 secondes
        QString messageA = pagesMessages["A"]; // Exemple : "15h45"
        int messageLengthA = messageA.length() * 6; // 6 points par caractère (police normale)
        int startPositionA = (80 - messageLengthA) / 2; // Centrer sur 80 colonnes
        if (startPositionA < 0) startPositionA = 0;
        QString positionHexA = QString("%1").arg(startPositionA, 2, 16, QChar('0')).toUpper();
        QByteArray pageDataA = QString("<ID01><L1><PA><FA><MB><WC><FA><CB><N%1>%2")
                               .arg(positionHexA).arg(messageA).toUtf8();
        unsigned char checksumA = calculateChecksum(pageDataA.mid(6));
        pageDataA.append(QString("%1").arg(checksumA, 2, 16, QChar('0')).toUpper());
        pageDataA.append("<E>");
        qDebug() << "Trame pour page A :" << pageDataA;
        if (!sendData(pageDataA)) {
            qDebug() << "Échec de l'envoi de la page A";
            return;
        }
        QThread::msleep(500);

        // Page B : salle centrée, orange fixe, 2 secondes
        QString messageB = pagesMessages["B"]; // Exemple : "3C01"
        int messageLengthB = messageB.length() * 6;
        int startPositionB = (80 - messageLengthB) / 2;
        if (startPositionB < 0) startPositionB = 0;
        QString positionHexB = QString("%1").arg(startPositionB, 2, 16, QChar('0')).toUpper();
        QByteArray pageDataB = QString("<ID01><L1><PB><FA><MA><WC><FA><CI><N%1>%2")
                               .arg(positionHexB).arg(messageB).toUtf8();
        unsigned char checksumB = calculateChecksum(pageDataB.mid(6));
        pageDataB.append(QString("%1").arg(checksumB, 2, 16, QChar('0')).toUpper());
        pageDataB.append("<E>");
        qDebug() << "Trame pour page B :" << pageDataB;
        if (!sendData(pageDataB)) {
            qDebug() << "Échec de l'envoi de la page B";
            return;
        }
        QThread::msleep(500);

        // Page C : matière-classe avec défilement, orange, 2 secondes
        QString messageC = pagesMessages["C"]; // Exemple : "Maths-1A"
        QByteArray pageDataC = QString("<ID01><L1><PC><FE><MQ><WC><FA><CI>%1")
                               .arg(messageC).toUtf8();
        unsigned char checksumC = calculateChecksum(pageDataC.mid(6));
        pageDataC.append(QString("%1").arg(checksumC, 2, 16, QChar('0')).toUpper());
        pageDataC.append("<E>");
        qDebug() << "Trame pour page C :" << pageDataC;
        if (!sendData(pageDataC)) {
            qDebug() << "Échec de l'envoi de la page C";
            return;
        }
        QThread::msleep(500);
    }

    // Étape 3 : Définir la page A comme page par défaut
    QByteArray defaultPageData = "<ID01><RPA>";
    unsigned char defaultChecksum = calculateChecksum(defaultPageData.mid(6));
    defaultPageData.append(QString("%1").arg(defaultChecksum, 2, 16, QChar('0')).toUpper());
    defaultPageData.append("<E>");
    qDebug() << "Trame pour définir page A par défaut :" << defaultPageData;
    if (!sendData(defaultPageData)) {
        qDebug() << "Échec de la définition de la page A par défaut";
        return;
    }
    QThread::msleep(500);

    // Étape 4 : Configurer la programmation horaire (affichage en boucle A, B, C)
    QByteArray scheduleData = "<ID01><TA>00000000009900000000ABC";
    unsigned char scheduleChecksum = calculateChecksum(scheduleData.mid(6));
    scheduleData.append(QString("%1").arg(scheduleChecksum, 2, 16, QChar('0')).toUpper());
    scheduleData.append("<E>");
    qDebug() << "Trame de programmation :" << scheduleData;
    if (!sendData(scheduleData)) {
        qDebug() << "Échec de la programmation horaire.";
        return;
    }
    QThread::msleep(500);

    // Étape 5 : Activer l'affichage
    QByteArray enableData = "<ID01><BF>";
    unsigned char enableChecksum = calculateChecksum(enableData.mid(6));
    enableData.append(QString("%1").arg(enableChecksum, 2, 16, QChar('0')).toUpper());
    enableData.append("<E>");
    qDebug() << "Trame d'activation :" << enableData;
    if (!sendData(enableData)) {
        qDebug() << "Échec de l'activation.";
        return;
    }
    qDebug() << "Programmation terminée avec succès.";
}

void Dialog::updateDisplay()
{
    // Relire la configuration depuis le fichier JSON à chaque actualisation
    if (!loadConfig()) {
        qDebug() << "Échec du rechargement de config.json, utilisation des valeurs actuelles";
        // Continuer avec les valeurs actuelles de portName et salle
    }

    if (!serialPort->isOpen()) {
        if (!openSerialPort()) {
            qDebug() << "Échec de l'ouverture du port série, abandon de l'actualisation";
            return;
        }
    }

    // Mettre à jour l'horloge temps réel de l'afficheur
    if (!sendClockUpdate()) {
        qDebug() << "Échec du réglage de l'horloge, poursuite de l'actualisation.";
        // Ne pas arrêter l'actualisation en cas d'échec, car ce n'est pas critique
    }

    // Construire l'URL avec la salle chargée
    QString url = QString("http://192.168.8.152:8000/reservations/?salle=%1&croissant=true").arg(salle);
    qDebug() << "Envoi de la requête API pour la salle :" << salle << ", URL :" << url;
    QNetworkRequest request;
    request.setUrl(QUrl(url));
    QNetworkReply *reply = manager->get(request);
    QEventLoop loop;
    connect(reply, &QNetworkReply::finished, &loop, &QEventLoop::quit);
    loop.exec();

    if (reply->error() == QNetworkReply::NoError) {
        QByteArray responseData = reply->readAll();
        qDebug() << "Réponse API reçue :" << responseData;
        reply->deleteLater();
        QJsonDocument jsonDoc = QJsonDocument::fromJson(responseData);

        if (!jsonDoc.isArray() || jsonDoc.array().isEmpty()) {
            // Si la réponse est vide ou invalide, afficher "Salle libre"
            QMap<QString, QString> pagesMessages = {
                {"A", "Salle libre"},
                {"B", "Salle libre"},
                {"C", "Salle libre"}
            };
            qDebug() << "Réponse API vide ou invalide, affichage : Salle libre";
            sendNextPage(pagesMessages);
            return;
        }

        // Date et heure actuelles
        QDateTime now = QDateTime::currentDateTime();
        QDate today = QDate::currentDate();

        QJsonArray reservations = jsonDoc.array();
        QList<QDateTime> todayStarts;
        QMap<QDateTime, QJsonObject> todayReservations;
        QJsonObject ongoingReservation;
        bool hasOngoingReservation = false;

        // Parcourir toutes les réservations
        for (const QJsonValue &value : reservations) {
            QJsonObject obj = value.toObject();
            QString dateStr = obj["date"].toString();
            QString heureDebutStr = obj["heure_debut"].toString();
            double duree = obj["duree"].toDouble();

            // Vérifier que la salle correspond
            QString numeroSalle = obj["numero_salle"].toString();
            if (numeroSalle != salle) {
                qDebug() << "Réservation ignorée pour la salle" << numeroSalle << "(attendu :" << salle << ")";
                continue;
            }

            // Parser la date
            QDate date = QDate::fromString(dateStr, "yyyy-MM-dd");

            // Vérifier si la réservation est pour aujourd'hui
            if (date != today) {
                continue;
            }

            // Parser l'heure de début
            int posH = heureDebutStr.indexOf('H');
            int posM = heureDebutStr.indexOf('M', posH);
            int heure = heureDebutStr.mid(2, posH - 2).toInt();
            int minutes = 0;
            if (posM > posH) {
                minutes = heureDebutStr.mid(posH + 1, posM - posH - 1).toInt();
            }

            QTime time(heure, minutes);
            QDateTime startTime(date, time);

            // Calculer l'heure de fin
            int durationMinutes = qRound(duree * 60);
            QDateTime endTime = startTime.addSecs(durationMinutes * 60);

            // Vérifier si la réservation est en cours
            if (now >= startTime && now < endTime) {
                ongoingReservation = obj;
                hasOngoingReservation = true;
            }
            // Vérifier si la réservation est dans le futur
            else if (startTime > now) {
                todayStarts.append(startTime);
                todayReservations[startTime] = obj;
            }
        }

        if (hasOngoingReservation) {
            // Afficher la réservation en cours
            auto removeAccents = [](const QString &str) {
                QString normalized = str.normalized(QString::NormalizationForm_D);
                QString result;
                for (const QChar &c : normalized) {
                    if (c.category() != QChar::Mark_NonSpacing) {
                        result.append(c);
                    }
                }
                return result;
            };

            QString messageA = QDateTime(today, QTime(ongoingReservation["heure_debut"].toString().mid(2, 2).toInt(),
                                                     ongoingReservation["heure_debut"].toString().mid(5, 2).toInt()))
                                .toString("HH'h'mm");
            QString messageB = ongoingReservation["numero_salle"].toString();
            QString nomMatiere = removeAccents(ongoingReservation["nom_matiere"].toString());
            QString nomsClasses = ongoingReservation["noms_classes"].toString();
            QString messageC = nomMatiere + "-" + nomsClasses;

            QMap<QString, QString> pagesMessages = {
                {"A", messageA},
                {"B", messageB},
                {"C", messageC}
            };
            qDebug() << "Réservation en cours détectée, affichage :";
            qDebug() << "Message A :" << messageA;
            qDebug() << "Message B :" << messageB;
            qDebug() << "Message C :" << messageC;
            sendNextPage(pagesMessages);
        } else if (!todayStarts.isEmpty()) {
            // Trier pour trouver la plus proche réservation future
            std::sort(todayStarts.begin(), todayStarts.end());
            QDateTime nextStart = todayStarts.first();
            QJsonObject nextReservation = todayReservations[nextStart];

            // Préparer les messages
            auto removeAccents = [](const QString &str) {
                QString normalized = str.normalized(QString::NormalizationForm_D);
                QString result;
                for (const QChar &c : normalized) {
                    if (c.category() != QChar::Mark_NonSpacing) {
                        result.append(c);
                    }
                }
                return result;
            };

            QString messageA = nextStart.toString("HH'h'mm");
            QString messageB = nextReservation["numero_salle"].toString();
            QString nomMatiere = removeAccents(nextReservation["nom_matiere"].toString());
            QString nomsClasses = nextReservation["noms_classes"].toString();
            QString messageC = nomMatiere + "-" + nomsClasses;

            QMap<QString, QString> pagesMessages = {
                {"A", messageA},
                {"B", messageB},
                {"C", messageC}
            };
            qDebug() << "Prochaine réservation détectée, affichage :";
            qDebug() << "Message A :" << messageA;
            qDebug() << "Message B :" << messageB;
            qDebug() << "Message C :" << messageC;
            sendNextPage(pagesMessages);
        } else {
            // Aucune réservation en cours ni future pour aujourd'hui, afficher "Salle libre"
            QMap<QString, QString> pagesMessages = {
                {"A", "Salle libre"},
                {"B", "Salle libre"},
                {"C", "Salle libre"}
            };
            qDebug() << "Aucune réservation en cours ni future pour la salle" << salle << ", affichage : Salle libre";
            sendNextPage(pagesMessages);
        }
    } else {
        // Gérer les erreurs API
        QString errorString = reply->errorString();
        qDebug() << "Erreur API pour la salle" << salle << ":" << errorString;
        reply->deleteLater();

        if (errorString.contains("Gone", Qt::CaseInsensitive)) {
            // Cas spécifique : erreur "Gone" signifie aucune réservation, afficher "Salle libre"
            QMap<QString, QString> pagesMessages = {
                {"A", "Salle libre"},
                {"B", "Salle libre"},
                {"C", "Salle libre"}
            };
            qDebug() << "Erreur API 'Gone' détectée, affichage : Salle libre";
            sendNextPage(pagesMessages);
        } else {
            // Autres erreurs API
            QMessageBox::critical(this, "Erreur", "Erreur API : " + errorString);
        }
    }
}

void Dialog::on_eraseAll_clicked()
{
    if (!serialPort->isOpen()) {
        qDebug() << "Port série non ouvert, tentative de réouverture.";
        if (!openSerialPort()) {
            QMessageBox::critical(this, "Erreur", "Impossible d'ouvrir le port série.");
            return;
        }
    }

    // Créer la trame pour effacer toute la mémoire
    QByteArray eraseData = "<ID01><D*>";
    unsigned char checksum = calculateChecksum(eraseData.mid(6)); // Calculer le checksum sur <D*>
    eraseData.append(QString("%1").arg(checksum, 2, 16, QChar('0')).toUpper());
    eraseData.append("<E>");

    qDebug() << "Trame d'effacement total :" << eraseData;

    // Envoyer la trame
    if (sendData(eraseData)) {
        QMessageBox::information(this, "Succès", "Mémoire de l'afficheur effacée avec succès.");
    } else {
        QMessageBox::critical(this, "Erreur", "Échec de l'effacement de la mémoire : " + lastResponse);
    }
}
