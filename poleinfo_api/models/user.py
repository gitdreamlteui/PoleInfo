"""Fonctions d'accès aux données utilisateur"""
from db.database import get_db_cursor
from core.security import verify_password

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
