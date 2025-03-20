import mysql.connector
from mysql.connector import Error

try:
    connection = mysql.connector.connect(
        host='192.168.8.152',
        database='poleinfo',
        user='root',
        password='cielPOLEINFO25**'
    )

    if connection.is_connected():
        print("Connexion réussie à la base de données MySQL.")

        cursor = connection.cursor()
        requete = """SELECT 
    r.id_reservation, 
    r.duree, 
    r.date, 
    r.info,
    s.numero AS numero_salle,
    s.capacite AS capacite_salle,
    s.type AS type_salle,
    m.nom AS nom_matiere,
    c.heure_debut,
    u.nom AS nom_user,
    u.prenom,
    GROUP_CONCAT(cl.nom SEPARATOR ', ') AS noms_classes
FROM reservation r
LEFT JOIN salle s ON r.id_salle = s.id_salle
LEFT JOIN matiere m ON r.id_matiere = m.id_matiere
LEFT JOIN creneau c ON r.id_creneau = c.id_creneau
LEFT JOIN user u ON r.id_user = u.id_user
LEFT JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
LEFT JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
GROUP BY r.id_reservation, r.duree, r.date, r.info, s.numero, s.capacite, s.type, m.nom, c.heure_debut, u.nom, u.prenom

"""
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
