#include "dialog.h"
#include "ui_dialog.h"
#include <QMessageBox>
#include <QDebug>
#include <QTimer>
#include <QJsonDocument>
#include <QJsonObject>
#include <QFile>
#include <QCoreApplication>
#include <QThread>

Dialog::Dialog(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::Dialog),
    serialPort(new QSerialPort(this)),
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
    if (!configObj.contains("port")) {
        qDebug() << "Erreur : config.json doit contenir la clé 'port'";
        configFile.close();
        return false; // Pas de valeurs par défaut
    }

    portName = configObj.value("port").toString();

    if (portName.isEmpty()) {
        qDebug() << "Erreur : la valeur 'port' dans config.json est vide";
        configFile.close();
        return false; // Pas de valeurs par défaut
    }

    qDebug() << "Configuration chargée : port =" << portName;
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
    int timeout = 15000; // Timeout porté à 15 secondes
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
        QThread::msleep(50); // Pause plus longue entre les vérifications
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

    // Étape 0 : Vérifier et définir l'adresse de l'afficheur à 01
    QByteArray setAddressData = "<ID><01><E>";
    qDebug() << "Trame de définition d'adresse :" << setAddressData;
    serialPort->write(setAddressData);
    QThread::msleep(2000); // Attendre une réponse ou stabilisation

    // Étape 1 : Réinitialisation logicielle
    QByteArray resetData = "<ID01><R>";
    unsigned char resetChecksum = calculateChecksum(resetData.mid(6));
    resetData.append(QString("%1").arg(resetChecksum, 2, 16, QChar('0')).toUpper());
    resetData.append("<E>");
    qDebug() << "Trame de réinitialisation :" << resetData;
    if (!sendData(resetData)) {
        qDebug() << "Échec de la réinitialisation.";
    }
    QThread::msleep(3000); // Attendre plus longtemps après réinitialisation

    // Étape 2 : Désactiver l'affichage
    QByteArray disableData = "<ID01><BE>";
    unsigned char disableChecksum = calculateChecksum(disableData.mid(6));
    disableData.append(QString("%1").arg(disableChecksum, 2, 16, QChar('0')).toUpper());
    disableData.append("<E>");
    qDebug() << "Trame de désactivation :" << disableData;
    if (!sendData(disableData)) {
        qDebug() << "Échec de la désactivation.";
        return;
    }
    QThread::msleep(2000);

    // Étape 3 : Effacer toute la mémoire (3 tentatives)
    QByteArray eraseAllData = "<ID01><D*>";
    unsigned char eraseChecksum = calculateChecksum(eraseAllData.mid(6));
    eraseAllData.append(QString("%1").arg(eraseChecksum, 2, 16, QChar('0')).toUpper());
    eraseAllData.append("<E>");
    for (int i = 0; i < 3; ++i) {
        qDebug() << "Tentative d'effacement total #" << (i + 1) << ":" << eraseAllData;
        if (sendData(eraseAllData)) {
            qDebug() << "Effacement réussi à la tentative #" << (i + 1);
            break;
        } else {
            qDebug() << "Échec de l'effacement à la tentative #" << (i + 1);
        }
        QThread::msleep(3000); // Pause longue entre les tentatives
    }

    // Étape 4 : Envoyer la page A (heure centrée, rouge clignotante)
    QString messageA = pagesMessages["A"];
    int messageLengthA = messageA.length() * 6;
    int startPositionA = (80 - messageLengthA) / 2;
    if (startPositionA < 0) startPositionA = 0;
    QString positionHexA = QString("%1").arg(startPositionA, 2, 16, QChar('0')).toUpper();
    QByteArray pageDataA = QString("<ID01><L1><PA><FA><MB><WC><FA><N%1><CA>%2")
                           .arg(positionHexA).arg(messageA).toUtf8();
    unsigned char checksumA = calculateChecksum(pageDataA.mid(6));
    pageDataA.append(QString("%1").arg(checksumA, 2, 16, QChar('0')).toUpper());
    pageDataA.append("<E>");
    qDebug() << "Trame pour page A :" << pageDataA;
    if (!sendData(pageDataA)) {
        qDebug() << "Échec de l'envoi de la page A";
        return;
    }
    QThread::msleep(2000);

    // Étape 5 : Envoyer la page B (message centré)
    QString messageB = pagesMessages["B"];
    int messageLengthB = messageB.length() * 6;
    int startPositionB = (80 - messageLengthB) / 2;
    if (startPositionB < 0) startPositionB = 0;
    QString positionHexB = QString("%1").arg(startPositionB, 2, 16, QChar('0')).toUpper();
    QByteArray pageDataB = QString("<ID01><L1><PB><FA><MA><WC><FA><N%1>%2")
                           .arg(positionHexB).arg(messageB).toUtf8();
    unsigned char checksumB = calculateChecksum(pageDataB.mid(6));
    pageDataB.append(QString("%1").arg(checksumB, 2, 16, QChar('0')).toUpper());
    pageDataB.append("<E>");
    qDebug() << "Trame pour page B :" << pageDataB;
    if (!sendData(pageDataB)) {
        qDebug() << "Échec de l'envoi de la page B";
        return;
    }
    QThread::msleep(2000);

    // Étape 6 : Envoyer la page C (message avec défilement)
    QString messageC = pagesMessages["C"];
    QByteArray pageDataC = QString("<ID01><L1><PC><FE><MQ><WC><FA>%1")
                           .arg(messageC).toUtf8();
    unsigned char checksumC = calculateChecksum(pageDataC.mid(6));
    pageDataC.append(QString("%1").arg(checksumC, 2, 16, QChar('0')).toUpper());
    pageDataC.append("<E>");
    qDebug() << "Trame pour page C :" << pageDataC;
    if (!sendData(pageDataC)) {
        qDebug() << "Échec de l'envoi de la page C";
        return;
    }
    QThread::msleep(2000);

    // Étape 7 : Configurer la programmation horaire
    QByteArray scheduleData = "<ID01><TA>00000000009900000000ABC";
    unsigned char scheduleChecksum = calculateChecksum(scheduleData.mid(6));
    scheduleData.append(QString("%1").arg(scheduleChecksum, 2, 16, QChar('0')).toUpper());
    scheduleData.append("<E>");
    qDebug() << "Trame de programmation :" << scheduleData;
    if (!sendData(scheduleData)) {
        qDebug() << "Échec de la programmation horaire.";
        return;
    }
    QThread::msleep(2000);

    // Étape 8 : Activer l'affichage
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
        // Continuer avec les valeurs actuelles de portName
    }

    if (!serialPort->isOpen()) {
        if (!openSerialPort()) {
            qDebug() << "Échec de l'ouverture du port série, abandon de l'actualisation";
            return;
        }
    }

    // Affichage par défaut
    QMap<QString, QString> pagesMessages = {
        {"A", QDateTime::currentDateTime().toString("HH'h'mm")},
        {"B", "Info"},
        {"C", "Salle libre"}
    };
    qDebug() << "Affichage par défaut :";
    qDebug() << "Message A :" << pagesMessages["A"];
    qDebug() << "Message B :" << pagesMessages["B"];
    qDebug() << "Message C :" << pagesMessages["C"];
    sendNextPage(pagesMessages);
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

    // Étape 0 : Vérifier et définir l'adresse de l'afficheur à 01
    QByteArray setAddressData = "<ID><01><E>";
    qDebug() << "Trame de définition d'adresse :" << setAddressData;
    serialPort->write(setAddressData);
    QThread::msleep(2000);

    // Étape 1 : Réinitialisation logicielle
    QByteArray resetData = "<ID01><R>";
    unsigned char resetChecksum = calculateChecksum(resetData.mid(6));
    resetData.append(QString("%1").arg(resetChecksum, 2, 16, QChar('0')).toUpper());
    resetData.append("<E>");
    qDebug() << "Trame de réinitialisation :" << resetData;
    if (!sendData(resetData)) {
        qDebug() << "Échec de la réinitialisation.";
        QMessageBox::warning(this, "Attention", "Réinitialisation échouée, poursuite de l'effacement.");
    }
    QThread::msleep(3000);

    // Étape 2 : Désactiver l'affichage
    QByteArray disableData = "<ID01><BE>";
    unsigned char disableChecksum = calculateChecksum(disableData.mid(6));
    disableData.append(QString("%1").arg(disableChecksum, 2, 16, QChar('0')).toUpper());
    disableData.append("<E>");
    qDebug() << "Trame de désactivation :" << disableData;
    if (!sendData(disableData)) {
        qDebug() << "Échec de la désactivation.";
        QMessageBox::critical(this, "Erreur", "Échec de la désactivation de l'affichage.");
        return;
    }
    QThread::msleep(2000);

    // Étape 3 : Effacer toute la mémoire (3 tentatives)
    QByteArray eraseData = "<ID01><D*>";
    unsigned char eraseChecksum = calculateChecksum(eraseData.mid(6));
    eraseData.append(QString("%1").arg(eraseChecksum, 2, 16, QChar('0')).toUpper());
    eraseData.append("<E>");
    bool eraseSuccess = false;
    for (int i = 0; i < 3; ++i) {
        qDebug() << "Tentative d'effacement total #" << (i + 1) << ":" << eraseData;
        if (sendData(eraseData)) {
            qDebug() << "Effacement réussi à la tentative #" << (i + 1);
            eraseSuccess = true;
            break;
        } else {
            qDebug() << "Échec de l'effacement à la tentative #" << (i + 1);
        }
        QThread::msleep(3000);
    }
    if (!eraseSuccess) {
        QMessageBox::critical(this, "Erreur", "Échec de l'effacement après 3 tentatives : " + lastResponse);
        return;
    }

    // Étape 4 : Réactiver l'affichage
    QByteArray enableData = "<ID01><BF>";
    unsigned char enableChecksum = calculateChecksum(enableData.mid(6));
    enableData.append(QString("%1").arg(enableChecksum, 2, 16, QChar('0')).toUpper());
    enableData.append("<E>");
    qDebug() << "Trame d'activation :" << enableData;
    if (!sendData(enableData)) {
        qDebug() << "Échec de l'activation.";
        QMessageBox::critical(this, "Erreur", "Échec de la réactivation de l'affichage.");
        return;
    }

    QMessageBox::information(this, "Succès", "Mémoire de l'afficheur effacée et affichage réactivé avec succès.");
}
