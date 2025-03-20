import mysql.connector
from mysql.connector import Error

try:
    connection = mysql.connector.connect(
        host='192.168.8.152',        # Hôte
        database='poleinfo',      # Nom DB
        user='root',     # Nom d'utilisateur
        password='cielPOLEINFO25**' # Mot de passe
    )

    if connection.is_connected():
        print("Connexion réussie à la base de données MySQL.")

        cursor = connection.cursor()
        requete = "SELECT * FROM reservation"
        cursor.execute(requete)
        resultats = cursor.fetchall()

        for ligne in resultats:
            print(ligne)

except Error as e:
    print(f"Erreur lors de la connexion à MySQL : {e}")

finally:
    if connection.is_connected():
        cursor.close()
        connection.close()
        print("Connexion MySQL fermée.")
