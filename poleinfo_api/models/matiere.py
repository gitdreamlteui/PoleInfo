"""Fonctions d'accès aux données des matières"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_matieres() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT nom FROM matiere"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

