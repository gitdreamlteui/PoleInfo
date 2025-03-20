"""Fonctions d'accès aux données des réservations"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_reservations() -> List[Dict[str, Any]]:
    """Récupère toutes les réservations"""
    with get_db_cursor() as cursor:
        query = """
        SELECT id_reservation, id_salle, id_matiere, id_creneau, 
               id_user, duree, date, info
        FROM reservation
        """
        cursor.execute(query)
        return cursor.fetchall()

def get_reservations_by_user(user_id: int) -> List[Dict[str, Any]]:
    """Récupère les réservations d'un utilisateur spécifique"""
    with get_db_cursor() as cursor:
        query = """
        SELECT id_reservation, id_salle, id_matiere, id_creneau, 
               id_user, duree, date, info
        FROM reservation
        WHERE id_user = %s
        """
        cursor.execute(query, (user_id,))
        return cursor.fetchall()
