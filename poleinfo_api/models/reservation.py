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
ORDER BY r.date ASC, c.heure_debut ASC
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
    
def post_reservation(duree, date, info, numero_salle, nom_matiere, heure_debut_creneau, login_user, nom_classe):
    with get_db_cursor() as cursor:
        try:
            query_reservation = """
            INSERT INTO reservation (
                duree, 
                date, 
                info, 
                id_salle, 
                id_matiere, 
                id_creneau, 
                id_user
            ) VALUES (
                %s,
                %s,
                %s,
                (SELECT id_salle FROM salle WHERE numero = %s),
                (SELECT id_matiere FROM matiere WHERE nom = %s),
                (SELECT id_creneau FROM creneau WHERE heure_debut = %s),
                (SELECT id_user FROM user WHERE login = %s)
            )"""
            
            cursor.execute(query_reservation, (float(duree), date, info, numero_salle, nom_matiere, heure_debut_creneau, login_user))
            reservation_id = cursor.lastrowid
            
            classes = [classe.strip() for classe in nom_classe.split(',')]
            
            for classe in classes:
                cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (classe,))
                classe_result = cursor.fetchone()
                if classe_result is None:
                    raise Exception(f"Classe '{classe}' non trouvée")
                    
                id_classe = classe_result['id_classe_grp']
                
                cursor.execute("INSERT INTO classe_reservation (id_reservation, id_classe_grp) VALUES (%s, %s)", 
                              (reservation_id, id_classe))
            
            return {"status": "success", "message": "Réservation créée avec succès", "id_reservation": reservation_id}
        
        except Exception as e:
            return {"status": "error", "message": f"Erreur lors de la création de la réservation: {str(e)}"}

def get_reservations_by_prof_increase(prof: str) -> List[Dict[str, Any]]:
    """Récupère toutes les réservations pour un professeur spécifique par ordre croissant"""
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
        JOIN salle s ON r.id_salle = s.id_salle
        JOIN matiere m ON r.id_matiere = m.id_matiere
        JOIN creneau c ON r.id_creneau = c.id_creneau
        JOIN user u ON r.id_user = u.id_user
        LEFT JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
        LEFT JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
        WHERE u.nom LIKE %s
        GROUP BY r.id_reservation, r.duree, r.date, r.info, s.numero, s.capacite, s.type, m.nom, c.heure_debut, u.nom, u.prenom
        ORDER BY r.date ASC, c.heure_debut ASC
        """
        cursor.execute(query, (prof,))
        return cursor.fetchall()
