"""Fonctions d'accès aux données des salles"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_salles() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT * FROM salles"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

