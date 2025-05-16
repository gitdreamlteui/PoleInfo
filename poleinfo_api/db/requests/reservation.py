"""Fonctions d'accès aux données des réservations"""
from db.database import get_db_cursor
from typing import List, Dict, Any, Optional
from datetime import datetime, timedelta
from datetime import time
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
            # Normalisation des données
            if isinstance(date, str):
                date = datetime.strptime(date, "%Y-%m-%d").date()
            
            if isinstance(heure_debut_creneau, str):
                heure_debut_creneau_obj = datetime.strptime(heure_debut_creneau, "%H:%M").time()
            else:
                heure_debut_creneau_obj = heure_debut_creneau
            
            duree = float(duree) if isinstance(duree, str) else duree
            
            # Vérification des horaires possibles
            heure_fin = (datetime.combine(date, heure_debut_creneau_obj) + timedelta(hours=duree)).time()
            
            # Vérifier si la réservation ne dépasse pas 17h25
            limite_fin_journee = datetime.time(17, 25)
            if heure_fin > limite_fin_journee:
                return {"status": "error_overtime", "message": "L'horaire ne peut pas dépasser 17h25"}
            
            # Vérifier si la réservation ne dépasse pas 12h35 si elle commence le matin
            limite_midi = datetime.time(12, 0)
            limite_midi_fin = datetime.time(12, 35)
            if heure_debut_creneau_obj < limite_midi and heure_fin > limite_midi_fin:
                return {"status": "error_overtime_midi", "message": "L'horaire ne peut pas dépasser 12h35"}
            
            # Vérifier les réservations existantes
            query_check_salle = """
            SELECT c.heure_debut, r.duree
            FROM reservation r
            JOIN salle s ON r.id_salle = s.id_salle
            JOIN creneau c ON r.id_creneau = c.id_creneau
            WHERE s.numero = %s AND r.date = %s
            """
            cursor.execute(query_check_salle, (numero_salle, date))
            existing_reservations = cursor.fetchall()
            
            # Calculer le début et la fin du nouveau créneau
            nouvelle_debut = datetime.combine(date, heure_debut_creneau_obj)
            nouvelle_fin = nouvelle_debut + timedelta(hours=duree)
            
            # Vérifier les chevauchements
            for reservation in existing_reservations:
                heure_debut_existante = reservation['heure_debut']
                duree_existante = reservation['duree']
                
                # Créer des datetime pour la comparaison
                existante_debut = datetime.combine(date, heure_debut_existante)
                existante_fin = existante_debut + timedelta(hours=duree_existante)
                
                # Vérifier le chevauchement
                if (nouvelle_debut < existante_fin and nouvelle_fin > existante_debut):
                    return {
                        "status": "error_reserv", 
                        "message": "Cette salle est déjà réservée pour cet horaire"
                    }
            
            # Vérifier si le créneau existe déjà, sinon l'insérer
            cursor.execute("SELECT id_creneau FROM creneau WHERE heure_debut = %s", 
                          (heure_debut_creneau_obj,))
            creneau_result = cursor.fetchone()
            
            if creneau_result:
                id_creneau = creneau_result['id_creneau']
            else:
                # Insérer un nouveau créneau (sans le champ 'jour' qui n'existe pas)
                cursor.execute("INSERT INTO creneau (heure_debut) VALUES (%s)", 
                              (heure_debut_creneau_obj,))
                id_creneau = cursor.lastrowid
            
            # Récupérer l'ID de la salle
            cursor.execute("SELECT id_salle FROM salle WHERE numero = %s", (numero_salle,))
            salle_result = cursor.fetchone()
            if not salle_result:
                return {"status": "error", "message": "Numéro de salle invalide"}
            id_salle = salle_result['id_salle']
            
            # Récupérer l'ID de la matière
            cursor.execute("SELECT id_matiere FROM matiere WHERE nom = %s", (nom_matiere,))
            matiere_result = cursor.fetchone()
            if not matiere_result:
                cursor.execute("INSERT INTO matiere (nom) VALUES (%s)", (nom_matiere,))
                id_matiere = cursor.lastrowid
            else:
                id_matiere = matiere_result['id_matiere']
            
            # Récupérer l'ID de l'utilisateur
            cursor.execute("SELECT id_user FROM user WHERE login = %s", (login_user,))
            user_result = cursor.fetchone()
            if not user_result:
                return {"status": "error", "message": "Utilisateur invalide"}
            id_user = user_result['id_user']
            
            # Insérer la réservation - ajuster selon la structure exacte de la table
            cursor.execute(
                "INSERT INTO reservation (id_salle, id_matiere, id_creneau, id_user, duree, date, info) "
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
                (id_salle, id_matiere, id_creneau, id_user, duree, date, info or "")
            )
            id_reservation = cursor.lastrowid
            
            # Associer la classe à la réservation
            cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (nom_classe,))
            classe_result = cursor.fetchone()
            if classe_result:
                cursor.execute(
                    "INSERT INTO classe_reservation (id_reservation, id_classe_grp) VALUES (%s, %s)",
                    (id_reservation, classe_result['id_classe_grp'])
                )
            else:
                return {"status": "warning", "message": "Classe introuvable, la réservation a été créée sans classe associée", "id_reservation": id_reservation}
            
            return {"status": "success", "id_reservation": id_reservation}

        except Exception as e:
            import traceback
            traceback.print_exc()
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