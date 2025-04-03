"""Fonctions d'accès aux classes"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_classes() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT nom FROM classe"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

