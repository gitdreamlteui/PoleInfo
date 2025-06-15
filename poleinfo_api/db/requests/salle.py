#db/requests/salle.py


"""Fonctions d'accès aux données des salles"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_salles() -> List[Dict[int, Any]]:
    """Récupère toute les salles"""
    with get_db_cursor() as cursor:
        query = "SELECT * FROM salle"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def get_salle_by_numero(numero: str) -> Dict[str, Any]:
    """Récupère un ID de salle par son numéro"""
    with get_db_cursor() as cursor:
        query = "SELECT id_salle, numero FROM salle WHERE numero = %s"
        cursor.execute(query, (numero,))
        result = cursor.fetchone()
    return result

def remove_salle(numero: str) -> bool:
    """Supprime une salle par son numéro"""
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
    

def create_salle(numero, capacite, type):
    """Créer une salle"""
    with get_db_cursor() as cursor:
        query = """
            INSERT INTO salle (numero, capacite, type)
            VALUES (%s, %s, %s)
        """
        values = (numero, capacite, type)
        
        cursor.execute(query, values)
        
        cursor.execute("SELECT LAST_INSERT_ID() as id_salle")
        result = cursor.fetchone()
        
        if result and 'id_salle' in result:
            user_id = result['id_salle']
            return user_id
        else:
            raise ValueError("Impossible de récupérer l'ID de la salle crée")