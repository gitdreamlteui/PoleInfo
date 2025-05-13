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
        with get_db_cursor() as cursor:
            # Trouver l'ID de la salle
            cursor.execute("SELECT id_salle FROM salle WHERE numero = %s", (numero_salle,))
            salle_result = cursor.fetchone()
            if not salle_result:
                return {
                    "conflict": True,
                    "message": f"Salle {numero_salle} non trouvée dans la base de données",
                    "conflict_type": "error",
                    "conflicting_reservations": []
                }
            id_salle = salle_result["id_salle"]
            
            # Trouver l'ID du créneau le plus proche
            cursor.execute("SELECT id_creneau, heure_debut FROM creneau WHERE heure_debut = %s", (heure_debut_creneau,))
            creneau_result = cursor.fetchone()
            if not creneau_result:
                # Si l'heure exacte n'est pas trouvée, cherchons l'heure la plus proche
                cursor.execute("SELECT id_creneau, heure_debut FROM creneau ORDER BY heure_debut")
                all_creneaux = cursor.fetchall()
                
                heure_input = datetime.strptime(heure_debut_creneau, "%H:%M").time()
                
                closest_creneau = None
                min_diff = float('inf')
                
                for creneau in all_creneaux:
                    db_time = creneau["heure_debut"]
                    diff_minutes = abs(
                        (heure_input.hour * 60 + heure_input.minute) - 
                        (db_time.hour * 60 + db_time.minute)
                    )
                    
                    if diff_minutes < min_diff:
                        min_diff = diff_minutes
                        closest_creneau = creneau
                
                if closest_creneau:
                    creneau_result = closest_creneau
                else:
                    return {
                        "conflict": True,
                        "message": f"Aucun créneau ne correspond à l'heure {heure_debut_creneau}",
                        "conflict_type": "error",
                        "conflicting_reservations": []
                    }
            
            id_creneau = creneau_result["id_creneau"]
            heure_debut_db = creneau_result["heure_debut"]
            
            # Calculer l'heure de fin basée sur la durée
            heure_debut_minutes = heure_debut_db.hour * 60 + heure_debut_db.minute
            heure_fin_minutes = heure_debut_minutes + duree
            
            # 1. Vérifier les conflits pour la salle
            # Recherche des réservations pour la même salle le même jour
            sql_salle = """
            SELECT r.id_reservation, u.login, u.nom, u.prenom, s.numero, m.nom as nom_matiere, 
                  c.heure_debut, r.duree, cl.nom as nom_classe
            FROM reservation r
            JOIN user u ON r.id_user = u.id_user
            JOIN salle s ON r.id_salle = s.id_salle
            JOIN creneau c ON r.id_creneau = c.id_creneau
            JOIN matiere m ON r.id_matiere = m.id_matiere
            LEFT JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
            LEFT JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
            WHERE r.date = %s 
            AND r.id_salle = %s
            """
            
            cursor.execute(sql_salle, (date, id_salle))
            reservations_salle = cursor.fetchall()
            
            conflits_salle = []
            for res in reservations_salle:
                res_debut_minutes = res["heure_debut"].hour * 60 + res["heure_debut"].minute
                res_fin_minutes = res_debut_minutes + int(res["duree"] * 60)
                
                # Vérifier si les créneaux se chevauchent
                if (res_debut_minutes < heure_fin_minutes and res_fin_minutes > heure_debut_minutes):
                    conflits_salle.append(res)
            
            # 2. Vérifier les conflits pour la classe (si une classe est spécifiée)
            conflits_classe = []
            if nom_classe:
                # Trouver l'ID de la classe
                cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (nom_classe,))
                classe_result = cursor.fetchone()
                
                if classe_result:
                    id_classe = classe_result["id_classe_grp"]
                    
                    # Recherche des réservations pour la même classe le même jour
                    sql_classe = """
                    SELECT r.id_reservation, u.login, u.nom, u.prenom, s.numero, m.nom as nom_matiere, 
                          c.heure_debut, r.duree, cl.nom as nom_classe
                    FROM reservation r
                    JOIN user u ON r.id_user = u.id_user
                    JOIN salle s ON r.id_salle = s.id_salle
                    JOIN creneau c ON r.id_creneau = c.id_creneau
                    JOIN matiere m ON r.id_matiere = m.id_matiere
                    JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
                    JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
                    WHERE r.date = %s 
                    AND cl.id_classe_grp = %s
                    """
                    
                    cursor.execute(sql_classe, (date, id_classe))
                    reservations_classe = cursor.fetchall()
                    
                    for res in reservations_classe:
                        res_debut_minutes = res["heure_debut"].hour * 60 + res["heure_debut"].minute
                        res_fin_minutes = res_debut_minutes + int(res["duree"] * 60)
                        
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


def get_available_slots(date: str, numero_salle: str = None, nom_classe: str = None) -> Dict[str, Any]:
    """
    Obtient les créneaux disponibles pour une salle et/ou une classe à une date donnée.
    
    Args:
        date (str): Date pour laquelle vérifier les disponibilités (format YYYY-MM-DD)
        numero_salle (str, optional): Numéro de la salle à vérifier
        nom_classe (str, optional): Nom de la classe à vérifier
        
    Returns:
        Dict[str, Any]: Dictionnaire contenant les créneaux disponibles et occupés
    """
    try:
        with get_db_cursor() as cursor:
            # Récupérer tous les créneaux
            cursor.execute("SELECT id_creneau, heure_debut FROM creneau ORDER BY heure_debut")
            all_creneaux = cursor.fetchall()
            
            # Structure pour stocker les résultats
            result = {
                "available": True,
                "message": "",
                "available_slots": [],
                "occupied_slots": []
            }
            
            # Si aucune salle ni classe n'est spécifiée, retourner tous les créneaux comme disponibles
            if not numero_salle and not nom_classe:
                result["available_slots"] = [
                    {
                        "id_creneau": c["id_creneau"],
                        "heure_debut": c["heure_debut"].strftime("%H:%M")
                    } for c in all_creneaux
                ]
                return result
            
            # Vérifier les créneaux occupés pour la salle
            occupied_salle_slots = []
            if numero_salle:
                # Trouver l'ID de la salle
                cursor.execute("SELECT id_salle FROM salle WHERE numero = %s", (numero_salle,))
                salle_result = cursor.fetchone()
                
                if salle_result:
                    id_salle = salle_result["id_salle"]
                    
                    # Récupérer les réservations pour cette salle à cette date
                    sql_salle = """
                    SELECT r.id_reservation, c.id_creneau, c.heure_debut, r.duree, 
                           u.login, m.nom as matiere, cl.nom as classe
                    FROM reservation r
                    JOIN creneau c ON r.id_creneau = c.id_creneau
                    JOIN user u ON r.id_user = u.id_user
                    JOIN matiere m ON r.id_matiere = m.id_matiere
                    LEFT JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
                    LEFT JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
                    WHERE r.date = %s AND r.id_salle = %s
                    """
                    cursor.execute(sql_salle, (date, id_salle))
                    occupied_salle_slots = cursor.fetchall()
            
            # Vérifier les créneaux occupés pour la classe
            occupied_classe_slots = []
            if nom_classe:
                # Trouver l'ID de la classe
                cursor.execute("SELECT id_classe_grp FROM classe WHERE nom = %s", (nom_classe,))
                classe_result = cursor.fetchone()
                
                if classe_result:
                    id_classe = classe_result["id_classe_grp"]
                    
                    # Récupérer les réservations pour cette classe à cette date
                    sql_classe = """
                    SELECT r.id_reservation, c.id_creneau, c.heure_debut, r.duree, 
                           u.login, m.nom as matiere, s.numero as salle
                    FROM reservation r
                    JOIN creneau c ON r.id_creneau = c.id_creneau
                    JOIN user u ON r.id_user = u.id_user
                    JOIN matiere m ON r.id_matiere = m.id_matiere
                    JOIN salle s ON r.id_salle = s.id_salle
                    JOIN classe_reservation cr ON r.id_reservation = cr.id_reservation
                    JOIN classe cl ON cr.id_classe_grp = cl.id_classe_grp
                    WHERE r.date = %s AND cr.id_classe_grp = %s
                    """
                    cursor.execute(sql_classe, (date, id_classe))
                    occupied_classe_slots = cursor.fetchall()
            
            # Fusionner les créneaux occupés
            occupied_slots = {}
            
            for slot in occupied_salle_slots:
                if slot["id_creneau"] not in occupied_slots:
                    occupied_slots[slot["id_creneau"]] = {
                        "id_creneau": slot["id_creneau"],
                        "heure_debut": slot["heure_debut"].strftime("%H:%M"),
                        "reservations": []
                    }
                
                occupied_slots[slot["id_creneau"]]["reservations"].append({
                    "id_reservation": slot["id_reservation"],
                    "login_user": slot["login"],
                    "duree": slot["duree"],
                    "matiere": slot["matiere"],
                    "classe": slot.get("classe"),
                    "type": "salle"
                })
            
            for slot in occupied_classe_slots:
                if slot["id_creneau"] not in occupied_slots:
                    occupied_slots[slot["id_creneau"]] = {
                        "id_creneau": slot["id_creneau"],
                        "heure_debut": slot["heure_debut"].strftime("%H:%M"),
                        "reservations": []
                    }
                
                occupied_slots[slot["id_creneau"]]["reservations"].append({
                    "id_reservation": slot["id_reservation"],
                    "login_user": slot["login"],
                    "duree": slot["duree"],
                    "matiere": slot["matiere"],
                    "salle": slot["salle"],
                    "type": "classe"
                })
            
            # Déterminer les créneaux disponibles (ceux qui ne sont pas occupés)
            available_slots = []
            for creneau in all_creneaux:
                if creneau["id_creneau"] not in occupied_slots:
                    available_slots.append({
                        "id_creneau": creneau["id_creneau"],
                        "heure_debut": creneau["heure_debut"].strftime("%H:%M")
                    })
            
            result["available_slots"] = available_slots
            result["occupied_slots"] = list(occupied_slots.values())
            result["message"] = "Créneaux disponibles récupérés avec succès"
            
            if not available_slots:
                result["available"] = False
                if numero_salle and nom_classe:
                    result["message"] = f"Aucun créneau disponible pour la salle {numero_salle} et la classe {nom_classe} à cette date"
                elif numero_salle:
                    result["message"] = f"Aucun créneau disponible pour la salle {numero_salle} à cette date"
                elif nom_classe:
                    result["message"] = f"Aucun créneau disponible pour la classe {nom_classe} à cette date"
            
            return result
        
    except mysql.connector.Error as e:
        return {
            "available": False,
            "message": f"Erreur de base de données: {str(e)}",
            "available_slots": [],
            "occupied_slots": []
        }
    except Exception as e:
        return {
            "available": False,
            "message": f"Erreur inattendue: {str(e)}",
            "available_slots": [],
            "occupied_slots": []
        }
