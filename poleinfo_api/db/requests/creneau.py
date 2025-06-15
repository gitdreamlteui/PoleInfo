#db/requests/creneau.py


"""Fonctions d'accès aux données des créneaux"""
from db.database import get_db_cursor
from typing import List, Dict, Any
from datetime import timedelta

def get_all_creneaux() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT heure_debut FROM creneau"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def get_creneau_by_heure(heure_debut: timedelta) -> Dict[str, Any]:
    with get_db_cursor() as cursor:
        query = "SELECT id_creneau, heure_debut FROM creneau WHERE heure_debut = %s"
        cursor.execute(query, (heure_debut,))
        result = cursor.fetchone()
    return result

def remove_creneau(heure_debut: timedelta) -> bool:
    with get_db_cursor() as cursor:
        check_query = "SELECT id_creneau FROM creneau WHERE heure_debut = %s"
        cursor.execute(check_query, (heure_debut,))
        creneau = cursor.fetchone()
        
        if not creneau:
            return False
        
        creneau_id = creneau['id_creneau']
            
        delete_query = "DELETE FROM creneau WHERE id_creneau = %s"
        cursor.execute(delete_query, (creneau_id,))
        
        return cursor.rowcount > 0
    
def create_creneau(heure_debut):
    with get_db_cursor() as cursor:
        query = """
            INSERT INTO creneau (heure_debut)
            VALUES (%s)
        """
        values = (heure_debut,)
        
        cursor.execute(query, values)
        
        cursor.execute("SELECT LAST_INSERT_ID() as id_creneau")
        result = cursor.fetchone()
        
        if result and 'id_creneau' in result:
            user_id = result['id_creneau']
            return user_id
        else:
            raise ValueError("Impossible de récupérer l'ID du créneau créé")
