"""Fonctions d'accès aux données des matières"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_matieres() -> List[Dict[str, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT id_matiere, nom FROM matiere"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def get_matiere_by_nom(nom: str) -> Dict[str, Any]:
    with get_db_cursor() as cursor:
        query = "SELECT id, nom FROM matiere WHERE nom = %s"
        cursor.execute(query, (nom,))
        result = cursor.fetchone()
    return result

def delete_matiere(nom: str) -> bool:
    with get_db_cursor() as cursor:

        check_query = "SELECT id FROM matiere WHERE nom = %s"
        cursor.execute(check_query, (nom,))
        matiere = cursor.fetchone()
        
        if not matiere:
            return False
            
        delete_query = "DELETE FROM matiere WHERE nom = %s"
        cursor.execute(delete_query, (nom,))
        
        return cursor.rowcount > 0