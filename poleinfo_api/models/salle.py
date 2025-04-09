"""Fonctions d'accès aux données des salles"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_salles() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT * FROM salle"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def get_salle_by_nom(nom: str) -> Dict[str, Any]:
    with get_db_cursor() as cursor:
        query = "SELECT id_salle, nom FROM salle WHERE numero = %s"
        cursor.execute(query, (nom,))
        result = cursor.fetchone()
    return result

def delete_salle(numero: str) -> bool:
    with get_db_cursor() as cursor:
        check_query = "SELECT id_salle FROM salle WHERE numero = %s"
        cursor.execute(check_query, (numero,))
        salle = cursor.fetchone()
        
        if not salle:
            return False
        
        salle_id = salle['id_salle']
            
        delete_query = "DELETE FROM salle WHERE id_salle = %s"
        cursor.execute(delete_query, (salle_id,))
        
        return cursor.rowcount > 0
