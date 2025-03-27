"""Fonctions d'accès aux données des réservations"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_reservations() -> List[Dict[str, Any]]:
    """Récupère toutes les réservations"""
    with get_db_cursor() as cursor:
        query = """SELECT 
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
        cursor.execute(query)
        return cursor.fetchall()

def get_reservations_by_salle(numero_salle: str) -> List[Dict[str, Any]]:
    """Récupère toutes les réservations pour une salle spécifique"""
    with get_db_cursor() as cursor:
        query = """SELECT 
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
        WHERE s.numero = %s
        GROUP BY r.id_reservation, r.duree, r.date, r.info, s.numero, s.capacite, s.type, m.nom, c.heure_debut, u.nom, u.prenom
        """
        cursor.execute(query, (numero_salle,))
        return cursor.fetchall()
    
def get_reservations_by_salle_increase(numero_salle: str) -> List[Dict[str, Any]]:
    """Récupère toutes les réservations pour une salle spécifique par ordre croissant"""
    with get_db_cursor() as cursor:
        query = """SELECT 
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
        WHERE s.numero = %s
        GROUP BY r.id_reservation, r.duree, r.date, r.info, s.numero, s.capacite, s.type, m.nom, c.heure_debut, u.nom, u.prenom
        ORDER BY r.date ASC, c.heure_debut ASC
        """
        cursor.execute(query, (numero_salle,))
        return cursor.fetchall()
    
    
def post_reservation(reservation):
    with get_db_cursor() as cursor:
        query_ = """"""
    return None
