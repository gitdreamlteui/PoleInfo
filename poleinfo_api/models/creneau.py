"""Fonctions d'accès aux données des créneaux"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_creneaux() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT heure_debut FROM creneau"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

