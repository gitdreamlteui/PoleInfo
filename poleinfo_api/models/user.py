"""Fonctions d'accès aux données utilisateur"""
from db.database import get_db_cursor
from core.password import verify_password, hash_password

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
    """Crée un nouvel utilisateur dans la base de données  
    Returns:
        int: ID de l'utilisateur créé
    """
    hashed_password = hash_password(password)
    
    with get_db_cursor() as cursor:
        query = """
            INSERT INTO user (login, passwd, type, nom, prenom)
            VALUES (%s, %s, %s, %s, %s)
            RETURNING id_user
        """
        values = (login, hashed_password, type, nom, prenom)
        
        cursor.execute(query, values)
        user_id = cursor.fetchone()[0]
        return user_id
