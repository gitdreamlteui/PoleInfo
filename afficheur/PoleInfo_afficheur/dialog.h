#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include <QDialog>
#include <QtSerialPort/QSerialPort>
#include <QSerialPortInfo>
#include <QSerialPort>
#include <QMessageBox>
#include <QNetworkAccessManager>
#include <QNetworkReply>
#include <QThread>
QT_BEGIN_NAMESPACE
namespace Ui { class Dialog; }
QT_END_NAMESPACE

class Dialog : public QDialog {
    Q_OBJECT

public:
    Dialog(QWidget *parent = nullptr);
    ~Dialog();

private slots:
    void onSendButtonClicked();
    void receiveData();
    void updateDisplay();


    void on_eraseAll_clicked();

private:
    Ui::Dialog *ui;
    QSerialPort *serialPort;
    QTimer *receptionTimer;
    QNetworkAccessManager *manager;
    QTimer *timer;
    QByteArray lastResponse;
    QString salle;
    QString portName;


    bool openSerialPort();
    bool sendClockUpdate();
    bool loadConfig();
    void closeSerialPort();      // Ferme le port série
    bool sendData(const QByteArray &data);  // Envoie les données
    unsigned char calculateChecksum(const QByteArray &data);
    void sendNextPage(const QMap<QString, QString> &pagesMessages);
};

#endif // MAINWINDOW_H
