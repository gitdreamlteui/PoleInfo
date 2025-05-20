/********************************************************************************
** Form generated from reading UI file 'dialog.ui'
**
** Created by: Qt User Interface Compiler version 5.15.8
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_DIALOG_H
#define UI_DIALOG_H

#include <QtCore/QVariant>
#include <QtWidgets/QApplication>
#include <QtWidgets/QDialog>
#include <QtWidgets/QLabel>
#include <QtWidgets/QLineEdit>
#include <QtWidgets/QPushButton>

QT_BEGIN_NAMESPACE

class Ui_Dialog
{
public:
    QPushButton *pushButtonSend;
    QLineEdit *messageInput;
    QLabel *label_2;
    QLineEdit *messageInput2;
    QLineEdit *messageInput3;
    QPushButton *eraseAll;

    void setupUi(QDialog *Dialog)
    {
        if (Dialog->objectName().isEmpty())
            Dialog->setObjectName(QString::fromUtf8("Dialog"));
        Dialog->resize(174, 135);
        pushButtonSend = new QPushButton(Dialog);
        pushButtonSend->setObjectName(QString::fromUtf8("pushButtonSend"));
        pushButtonSend->setEnabled(false);
        pushButtonSend->setGeometry(QRect(24, 100, 121, 23));
        messageInput = new QLineEdit(Dialog);
        messageInput->setObjectName(QString::fromUtf8("messageInput"));
        messageInput->setEnabled(false);
        messageInput->setGeometry(QRect(30, 30, 113, 20));
        label_2 = new QLabel(Dialog);
        label_2->setObjectName(QString::fromUtf8("label_2"));
        label_2->setGeometry(QRect(60, 10, 47, 14));
        messageInput2 = new QLineEdit(Dialog);
        messageInput2->setObjectName(QString::fromUtf8("messageInput2"));
        messageInput2->setEnabled(false);
        messageInput2->setGeometry(QRect(30, 50, 113, 20));
        messageInput3 = new QLineEdit(Dialog);
        messageInput3->setObjectName(QString::fromUtf8("messageInput3"));
        messageInput3->setEnabled(false);
        messageInput3->setGeometry(QRect(30, 70, 113, 20));
        eraseAll = new QPushButton(Dialog);
        eraseAll->setObjectName(QString::fromUtf8("eraseAll"));
        eraseAll->setGeometry(QRect(150, 80, 80, 25));

        retranslateUi(Dialog);

        QMetaObject::connectSlotsByName(Dialog);
    } // setupUi

    void retranslateUi(QDialog *Dialog)
    {
        Dialog->setWindowTitle(QCoreApplication::translate("Dialog", "Dialog", nullptr));
        pushButtonSend->setText(QCoreApplication::translate("Dialog", "Envoie automatique", nullptr));
        messageInput->setText(QCoreApplication::translate("Dialog", "Bienvenue", nullptr));
        label_2->setText(QCoreApplication::translate("Dialog", "Afficheur", nullptr));
        messageInput2->setText(QCoreApplication::translate("Dialog", "PoleInfo", nullptr));
        messageInput3->setText(QCoreApplication::translate("Dialog", "LP2I", nullptr));
        eraseAll->setText(QCoreApplication::translate("Dialog", "PushButton", nullptr));
    } // retranslateUi

};

namespace Ui {
    class Dialog: public Ui_Dialog {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_DIALOG_H
