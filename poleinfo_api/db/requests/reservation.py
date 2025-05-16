"""Fonctions d'accès aux données des réservations"""
from db.database import get_db_cursor
from typing import List, Dict, Any, Optional
from datetime import datetime, timedelta

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

def get_reservation_by_id(id_reservation: int) -> Optional[Dict[str, Any]]:
    """Récupère une réservation spécifique par son identifiant"""
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
        WHERE r.id_reservation = %s
        GROUP BY r.id_reservation, r.duree, r.date, r.info, s.numero, s.capacite, s.type, m.nom, c.heure_debut, u.nom, u.prenom
        """
        cursor.execute(query, (id_reservation,))
        result = cursor.fetchone()
        return result


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
            query_check_salle = """
            SELECT c.heure_debut, r.duree
            FROM reservation r
            JOIN salle s ON r.id_salle = s.id_salle
            JOIN creneau c ON r.id_creneau = c.id_creneau
            WHERE s.numero = %s AND r.date = %s
            """
            cursor.execute(query_check_salle, (numero_salle, date))
            existing_reservations = cursor.fetchall()

            for existing_reservation in existing_reservations:
                heure_debut_existante = existing_reservation[0]
                duree_existante = existing_reservation[1]
        
                # Convertir l'heure de début en datetime
                heure_debut_existante_dt = datetime.combine(date, heure_debut_existante)
                fin_existante_dt = heure_debut_existante_dt + timedelta(hours=duree_existante)
                
                # Convertir la nouvelle réservation en datetime
                heure_debut_creneau_dt = datetime.combine(date, heure_debut_creneau)
                fin_nouvelle_dt = heure_debut_creneau_dt + timedelta(hours=duree_creneau)
                
                if not (fin_nouvelle_dt <= heure_debut_existante_dt or heure_debut_creneau_dt >= fin_existante_dt):
                    return {
                        "status": "error_reserv", 
                        "message": f"La salle {numero_salle} est déjà occupée à cette date et ce créneau horaire"
                    }
            
            cursor.execute("SELECT id_salle FROM salle WHERE numero = %s", (numero_salle,))
            result_salle = cursor.fetchone()
            if not result_salle:
                return {"status": "error", "message": f"Salle {numero_salle} non trouvée"}
            id_salle = result_salle['id_salle']

            cursor.execute("SELECT id_matiere FROM matiere WHERE nom = %s", (nom_matiere,))
            result_matiere = cursor.fetchone()
            if not result_matiere:
                return {"status": "error", "message": f"Matière {nom_matiere} non trouvée"}
            id_matiere = result_matiere['id_matiere']

            cursor.execute("SELECT id_creneau FROM creneau WHERE heure_debut = %s", (heure_debut_creneau,))
            result_creneau = cursor.fetchone()
            if not result_creneau:
                return {"status": "error", "message": f"Créneau {heure_debut_creneau} non trouvé"}
            id_creneau = result_creneau['id_creneau']

            cursor.execute("SELECT id_user FROM user WHERE login = %s", (login_user,))
            result_user = cursor.fetchone()
            if not result_user:
                return {"status": "error", "message": f"Utilisateur {login_user} non trouvé"}
            id_user = result_user['id_user']

            # Insérer la réservation
            cursor.execute("""
                INSERT INTO reservation (duree, date, info, id_salle, id_matiere, id_creneau, id_user)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """, (duree, date, info, id_salle, id_matiere, id_creneau, id_user))
            
            id_reservation = cursor.lastrowid

            # Ajouter les classes à la réservation
            classes = [classe.strip() for classe in nom_classe.split(',')]
            for classe in classes:
                cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (classe,))
                result_classe = cursor.fetchone()
                if not result_classe:
                    return {"status": "error", "message": f"Classe {classe} non trouvée"}
                id_classe = result_classe['id_classe_grp']
                cursor.execute("INSERT INTO classe_reservation (id_reservation, id_classe_grp) VALUES (%s, %s)",
                              (id_reservation, id_classe))
            
            return {"status": "success", "id_reservation": id_reservation}

        except Exception as e:
            return {"status": "error", "message": str(e)}


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

def remove_reservation(user_id: int, date: str, numero_salle: str, heure_debut: str):
    with get_db_cursor() as cursor:
        try:
            cursor.execute("""
                SELECT r.id_reservation FROM reservation r
                JOIN salle s ON r.id_salle = s.id_salle
                JOIN creneau c ON r.id_creneau = c.id_creneau
                WHERE r.id_user = %s AND r.date = %s AND s.numero = %s AND c.heure_debut = %s
            """, (user_id, date, numero_salle, heure_debut))
            
            result = cursor.fetchone()
            if not result:
                return {"status": "error", "message": "Aucune réservation ne correspond à ces critères"}
            
            id_reservation = result['id_reservation']
            
            cursor.execute("""
                DELETE FROM reservation 
                WHERE id_reservation = %s
            """, (id_reservation,))
            
            return {"status": "success", "message": "Réservation supprimée avec succès"}
            
        except Exception as e:
            return {"status": "error", "message": f"Erreur lors de la suppression: {str(e)}"}
        

def remove_reservation_by_id(user_id: int, id_reservation: int):
    with get_db_cursor() as cursor:
        try:
            cursor.execute("""
                DELETE FROM reservation 
                WHERE id_reservation = %s
            """, (id_reservation,))
            
            return {"status": "success", "message": "Réservation supprimée avec succès"}
           
        except Exception as e:
            return {"status": "error", "message": f"Erreur lors de la suppression: {str(e)}"}


def update_reservation(id_reservation: int, duree=None, date=None, info=None, numero_salle=None,
                       nom_matiere=None, heure_debut_creneau=None, nom_classe=None):
    with get_db_cursor() as cursor:
        try:
            fields = []
            values = []

            if duree is not None:
                fields.append("duree = %s")
                values.append(duree)
            if date is not None:
                fields.append("date = %s")
                values.append(date)
            if info is not None:
                fields.append("info = %s")
                values.append(info)
            if numero_salle is not None:
                fields.append("id_salle = (SELECT id_salle FROM salle WHERE numero = %s)")
                values.append(numero_salle)
            if nom_matiere is not None:
                fields.append("id_matiere = (SELECT id_matiere FROM matiere WHERE nom = %s)")
                values.append(nom_matiere)
            if heure_debut_creneau is not None:
                fields.append("id_creneau = (SELECT id_creneau FROM creneau WHERE heure_debut = %s)")
                values.append(str(heure_debut_creneau))

            if fields:
                query = f"UPDATE reservation SET {', '.join(fields)} WHERE id_reservation = %s"
                values.append(id_reservation)
                cursor.execute(query, tuple(values))

            if nom_classe is not None:
                cursor.execute("DELETE FROM classe_reservation WHERE id_reservation = %s", (id_reservation,))
                for classe in nom_classe.split(","):
                    cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (classe.strip(),))
                    result = cursor.fetchone()
                    if not result:
                        raise Exception(f"Classe '{classe}' non trouvée")
                    cursor.execute("INSERT INTO classe_reservation (id_reservation, id_classe_grp) VALUES (%s, %s)",
                                   (id_reservation, result['id_classe_grp']))

            return {"status": "success", "message": "Réservation mise à jour"}
        except Exception as e:
            return {"status": "error", "message": f"Erreur lors de la mise à jour : {str(e)}"}