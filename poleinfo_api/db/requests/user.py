#db/requests/user.py


"""Fonctions d'accès aux données utilisateur"""
from db.database import get_db_cursor
from core.password import verify_password, hash_password
from typing import List, Dict, Any

def get_user_by_login(login):
    """Récupère un utilisateur par son login"""
    with get_db_cursor() as cursor:
        cursor.execute("SELECT * FROM user WHERE login = %s", (login,))
        user = cursor.fetchone()
        return user
    
def get_user_by_id(user_id):
    """Récupère un utilisateur par son ID"""
    with get_db_cursor() as cursor:
        cursor.execute("SELECT * FROM user WHERE id_user = %s", (user_id,))
        user = cursor.fetchone()
        return user

def authenticate_user(login, password):
    """Authentifie un utilisateur en vérifiant son login et son mot de passe"""
    user = get_user_by_login(login)
    if not user:
        return None
    
    if not verify_password(password, user["passwd"]):
        return None
        
    return user

def create_user(login, password, type, nom, prenom):
    """Créer un nouveau utilisateur"""
    hashed_password = hash_password(password)
    with get_db_cursor() as cursor:
        query = """
            INSERT INTO user (login, passwd, type, nom, prenom)
            VALUES (%s, %s, %s, %s, %s)
        """
        values = (login, hashed_password, type, nom, prenom)
        
        cursor.execute(query, values)
        
        cursor.execute("SELECT LAST_INSERT_ID() as id_user")
        result = cursor.fetchone()
        
        if result and 'id_user' in result:
            user_id = result['id_user']
            return user_id
        else:
            raise ValueError("Impossible de récupérer l'ID de l'utilisateur créé")

def get_all_users() -> List[Dict[str, Any]]:
    """Récupère tous les utilisateurs"""
    with get_db_cursor() as cursor:
        cursor.execute("SELECT * FROM user")
        results = list(cursor.fetchall())
        
        while cursor.nextset():
            pass
    return results

def delete_user_by_login(login):
    """Supprime un utilisateur"""
    with get_db_cursor() as cursor:
        cursor.execute("SELECT id_user FROM user WHERE login = %s", (login,))
        user = cursor.fetchone()

        user_id = user['id_user']
            
        delete_query = "DELETE FROM user WHERE id_user = %s"
        cursor.execute(delete_query, (user_id,))
        
        return cursor.rowcount > 0
