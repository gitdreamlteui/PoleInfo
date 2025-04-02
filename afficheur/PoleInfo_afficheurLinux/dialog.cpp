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

bool Dialog::openSerialPort()
{
    serialPort->setPortName("/dev/ttyUSB0");
    serialPort->setBaudRate(QSerialPort::Baud9600);
    serialPort->setDataBits(QSerialPort::Data8);
    serialPort->setParity(QSerialPort::NoParity);
    serialPort->setStopBits(QSerialPort::OneStop);
    serialPort->setFlowControl(QSerialPort::NoFlowControl);

    if (serialPort->open(QIODevice::ReadWrite)) {
        qDebug() << "Port série ouvert avec succès.";
        return true;
    } else {
        QString errorMsg = "Impossible d'ouvrir le port série : " + serialPort->errorString();
        if (serialPort->error() == QSerialPort::PermissionError) {
            errorMsg += "\nVérifiez les permissions sur /dev/ttyUSB0. Essayez 'sudo adduser $USER dialout' et redémarrez.";
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

    qint64 bytesWritten = serialPort->write(data);
    serialPort->waitForBytesWritten(1000);
    if (bytesWritten != data.size()) {
        qDebug() << "Erreur lors de l'envoi :" << serialPort->errorString();
        return false;
    }

    qDebug() << "Trame envoyée :" << data.toHex(' ');
    lastResponse.clear();
    int timeout = 2000;
    while (timeout > 0) {
        if (serialPort->waitForReadyRead(100)) {
            lastResponse += serialPort->readAll();
            qDebug() << "Fragment reçu :" << lastResponse;
            if (lastResponse.contains("ACK")) {
                qDebug() << "Réponse complète : ACK";
                return true;
            } else if (lastResponse.contains("NACK")) {
                qDebug() << "Réponse complète : NACK";
                return false;
            }
        }
        timeout -= 100;
        QThread::msleep(10);
    }

    qDebug() << "Erreur : réponse incomplète ou absente :" << lastResponse;
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
        qDebug() << "Réponse reçue (asynchrone) :" << response;
    }
}

void Dialog::sendNextPage(const QMap<QString, QString> &pagesMessages)
{
    // Étape 1 : Effacer toutes les programmations (A à E) pour éviter les résidus
    for (char prog = 'A'; prog <= 'B'; ++prog) {
        QByteArray clearData = QString("<ID01><DT%1>").arg(prog).toUtf8();
        unsigned char clearChecksum = calculateChecksum(clearData.mid(6));
        clearData.append(QString("%1").arg(clearChecksum, 2, 16, QChar('0')).toUpper());
        clearData.append("<B>");
        if (!sendData(clearData)) {
            qDebug() << "Échec de l'effacement de la programmation" << prog;
            return;
        }
        QThread::msleep(100);
    }

    // Étape 2 : Programmation horaire pour défilement de A, B, C
    QByteArray scheduleData = "<ID01><TA>00000000009900000000ABC";
    unsigned char scheduleChecksum = calculateChecksum(scheduleData.mid(6));
    scheduleData.append(QString("%1").arg(scheduleChecksum, 2, 16, QChar('0')).toUpper());
    scheduleData.append("<E>");
    if (!sendData(scheduleData)) {
        qDebug() << "Échec de la programmation horaire.";
        return;
    }
    QThread::msleep(100);

    // Étape 3 : Envoyer les pages avec défilement
    for (auto it = pagesMessages.constBegin(); it != pagesMessages.constEnd(); ++it) {
        QString page = it.key();
        QString message = it.value();
        // Animation : défilement de droite à gauche (<FE>) et disparition vers la droite (<FF>)
        QByteArray pageData = QString("<ID01><L1><P%1><FE><MQ><WC><FF>%2")
                              .arg(page).arg(message).toUtf8();
        unsigned char pageChecksum = calculateChecksum(pageData.mid(6));
        pageData.append(QString("%1").arg(pageChecksum, 2, 16, QChar('0')).toUpper());
        pageData.append("<E>");
        if (!sendData(pageData)) {
            qDebug() << "Échec de l'envoi de la page" << page;
            return;
        }
        QThread::msleep(100);
    }

    // Étape 4 : Activer l'affichage
    QByteArray enableData = "<ID01><BF>";
    unsigned char enableChecksum = calculateChecksum(enableData.mid(6));
    enableData.append(QString("%1").arg(enableChecksum, 2, 16, QChar('0')).toUpper());
    enableData.append("<E>");
    if (!sendData(enableData)) {
        qDebug() << "Échec de l'activation de l'affichage.";
    }
}

void Dialog::updateDisplay()
{
    if (!serialPort->isOpen()) {
        if (!openSerialPort()) {
            return;
        }
    }

    QNetworkRequest request(QUrl("http://192.168.8.152:8000/reservations/?salle=3C01&croissant=true"));
    QNetworkReply *reply = manager->get(request);
    QEventLoop loop;
    connect(reply, &QNetworkReply::finished, &loop, &QEventLoop::quit);
    loop.exec();

    if (reply->error() == QNetworkReply::NoError) {
        QByteArray responseData = reply->readAll();
        reply->deleteLater();
        QJsonDocument jsonDoc = QJsonDocument::fromJson(responseData);

        if (!jsonDoc.isArray() || jsonDoc.array().isEmpty()) {
            QMessageBox::warning(this, "Erreur", "Réponse de l'API invalide ou vide.");
            return;
        }

        QJsonObject jsonObj = jsonDoc.array()[0].toObject();

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

        QString heureDebutStr = jsonObj["heure_debut"].toString();
        int heure = heureDebutStr.mid(2, heureDebutStr.indexOf('H') - 2).toInt();
        QString messageA = QString("%1h00").arg(heure);
        QString messageB = jsonObj["numero_salle"].toString();
        QString nomMatiere = removeAccents(jsonObj["nom_matiere"].toString());
        QString nomsClasses = jsonObj["noms_classes"].toString();
        QString messageC = nomMatiere + "-" + nomsClasses;

        QMap<QString, QString> pagesMessages = {
            {"A", messageA},
            {"B", messageB},
            {"C", messageC}
        };
        sendNextPage(pagesMessages);
    } else {
        QMessageBox::critical(this, "Erreur", "Erreur API : " + reply->errorString());
        reply->deleteLater();
    }
}
