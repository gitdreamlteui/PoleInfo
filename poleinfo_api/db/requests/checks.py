"""
Module de vérification des conflits pour les réservations de salles.

Ce module contient des fonctions pour vérifier si une réservation peut être effectuée
en s'assurant qu'il n'y a pas de conflit avec des réservations existantes.
"""

from typing import Dict, Any, List
from datetime import datetime, time, timedelta
from db.database import get_db_cursor, get_db_connection
import mysql.connector

def check_reservation_conflicts(date: str, 
                               numero_salle: str, 
                               heure_debut_creneau: str, 
                               duree: int,
                               nom_classe: str = None) -> Dict[str, Any]:
    """
    Vérifie s'il existe déjà des réservations qui entreraient en conflit
    avec une nouvelle réservation.
    
    Args:
        date (str): Date de la réservation au format ISO (YYYY-MM-DD)
        numero_salle (str): Numéro de la salle à réserver
        heure_debut_creneau (str): Heure de début au format HH:MM
        duree (int): Durée en minutes de la réservation
        nom_classe (str, optional): Nom de la classe concernée par la réservation
    
    Returns:
        Dict[str, Any]: Dictionnaire contenant:
            - "conflict": True s'il y a un conflit, False sinon
            - "message": Message décrivant le conflit s'il existe
            - "conflict_type": Type de conflit (salle, classe, ou les deux)
            - "conflicting_reservations": Liste des réservations en conflit
    """
    try:
        # Convertir heure_debut_creneau en minutes depuis minuit
        heures, minutes = map(int, heure_debut_creneau.split(':'))
        heure_debut_minutes = heures * 60 + minutes
        heure_fin_minutes = heure_debut_minutes + int(duree)
        
        conflits_salle = []
        conflits_classe = []
        
        with get_db_cursor() as cursor:
            # Vérifier si la salle existe
            cursor.execute("SELECT id_salle FROM salle WHERE numero = %s", (numero_salle,))
            salle_result = cursor.fetchone()
            if not salle_result:
                return {
                    "conflict": True,
                    "message": f"La salle {numero_salle} n'existe pas",
                    "conflict_type": "error",
                    "conflicting_reservations": []
                }
            
            id_salle = salle_result["id_salle"]
            
            # Vérifier les conflits pour la salle
            sql_salle = """
            SELECT r.id_reservation, r.date, c.heure_debut, r.duree, u.login, u.prenom, u.nom, 
                   s.numero, m.nom_matiere, cl.nom_classe
            FROM reservation r
            JOIN user u ON r.id_user = u.id_user
            JOIN salle s ON r.id_salle = s.id_salle
            JOIN creneau c ON r.id_creneau = c.id_creneau
            JOIN matiere m ON r.id_matiere = m.id_matiere
            LEFT JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
            LEFT JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
            WHERE r.date = %s AND s.id_salle = %s
            """
            
            cursor.execute(sql_salle, (date, id_salle))
            reservations_salle = cursor.fetchall()
            
            for res in reservations_salle:
                res_debut_minutes = res["heure_debut"].hour * 60 + res["heure_debut"].minute
                res_fin_minutes = res_debut_minutes + int(res["duree"])
                
                # Vérifier si les créneaux se chevauchent
                if (res_debut_minutes < heure_fin_minutes and res_fin_minutes > heure_debut_minutes):
                    conflits_salle.append(res)
            
            # Vérifier les conflits pour la classe si spécifiée
            if nom_classe:
                # Vérifier si la classe existe
                cursor.execute("SELECT id_classe_grp FROM classe WHERE nom_classe = %s", (nom_classe,))
                classe_result = cursor.fetchone()
                if not classe_result:
                    return {
                        "conflict": True,
                        "message": f"La classe {nom_classe} n'existe pas",
                        "conflict_type": "error",
                        "conflicting_reservations": []
                    }
                
                id_classe = classe_result["id_classe_grp"]
                
                # Récupérer les réservations pour cette classe à la date spécifiée
                sql_classe = """
                SELECT r.id_reservation, r.date, c.heure_debut, r.duree, u.login, u.prenom, u.nom, 
                       s.numero, m.nom_matiere, cl.nom_classe
                FROM reservation r
                JOIN user u ON r.id_user = u.id_user
                JOIN salle s ON r.id_salle = s.id_salle
                JOIN creneau c ON r.id_creneau = c.id_creneau
                JOIN matiere m ON r.id_matiere = m.id_matiere
                JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
                JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
                WHERE r.date = %s AND cl.id_classe_grp = %s
                """
                
                cursor.execute(sql_classe, (date, id_classe))
                reservations_classe = cursor.fetchall()
                
                for res in reservations_classe:
                    res_debut_minutes = res["heure_debut"].hour * 60 + res["heure_debut"].minute
                    res_fin_minutes = res_debut_minutes + int(res["duree"])
                    
                    # Vérifier si les créneaux se chevauchent
                    if (res_debut_minutes < heure_fin_minutes and res_fin_minutes > heure_debut_minutes):
                        conflits_classe.append(res)
        
        # Préparer le résultat
        result = {
            "conflict": False,
            "message": "Aucun conflit détecté",
            "conflict_type": None,
            "conflicting_reservations": []
        }
        
        # Traiter les conflits de salle
        if conflits_salle:
            result["conflict"] = True
            result["conflict_type"] = "salle"
            result["message"] = f"La salle {numero_salle} est déjà réservée sur ce créneau."
            result["conflicting_reservations"].extend([
                {
                    "id_reservation": res["id_reservation"],
                    "login_user": res["login"],
                    "nom_prof": f"{res['prenom']} {res['nom']}",
                    "heure_debut": res["heure_debut"].strftime("%H:%M"),
                    "duree": res["duree"],
                    "nom_classe": res["nom_classe"],
                    "nom_matiere": res["nom_matiere"],
                    "type": "salle"
                } for res in conflits_salle
            ])
        
        # Traiter les conflits de classe
        if conflits_classe:
            result["conflict"] = True
            if result["conflict_type"]:
                result["conflict_type"] = "salle_et_classe"
                result["message"] += f" De plus, la classe {nom_classe} a déjà cours sur ce créneau."
            else:
                result["conflict_type"] = "classe"
                result["message"] = f"La classe {nom_classe} a déjà cours sur ce créneau."
            
            result["conflicting_reservations"].extend([
                {
                    "id_reservation": res["id_reservation"],
                    "login_user": res["login"],
                    "nom_prof": f"{res['prenom']} {res['nom']}",
                    "heure_debut": res["heure_debut"].strftime("%H:%M"),
                    "duree": res["duree"],
                    "numero_salle": res["numero"],
                    "nom_matiere": res["nom_matiere"],
                    "type": "classe"
                } for res in conflits_classe
            ])
        
        return result
    
    except mysql.connector.Error as e:
        return {
            "conflict": True,
            "message": f"Erreur de base de données lors de la vérification des conflits: {str(e)}",
            "conflict_type": "error",
            "conflicting_reservations": []
        }
    except Exception as e:
        return {
            "conflict": True,
            "message": f"Erreur inattendue: {str(e)}",
            "conflict_type": "error",
            "conflicting_reservations": []
        }
