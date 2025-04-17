"""Fonctions d'accès aux classes"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_classes() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT nom FROM classe"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def get_classe_by_nom(nom: str) -> Dict[str, Any]:
    with get_db_cursor() as cursor:
        query = "SELECT id_classe_grp, nom FROM classe WHERE nom = %s"
        cursor.execute(query, (nom,))
        result = cursor.fetchone()
    return result

def remove_classe(numero: str) -> bool:
    with get_db_cursor() as cursor:
        check_query = "SELECT id_classe_grp FROM classe WHERE nom = %s"
        cursor.execute(check_query, (numero,))
        classe = cursor.fetchone()
        
        if not classe:
            return False
        
        classe_id = classe['id_classe_grp']
            
        delete_query = "DELETE FROM classe WHERE id_classe = %s"
        cursor.execute(delete_query, (classe_id,))
        
        return cursor.rowcount > 0