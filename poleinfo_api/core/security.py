"""Fonctions de sécurité pour l'authentification"""
from passlib.context import CryptContext
import jwt
from fastapi import Depends, HTTPException, status
from datetime import datetime, timedelta
from config import SECRET_KEY, ALGORITHM, ACCESS_TOKEN_EXPIRE_MINUTES
from models.user import get_user_by_id
from core.auth import verify_token

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

def hash_password(password: str) -> str:
    """Hache un mot de passe en utilisant bcrypt"""
    return pwd_context.hash(password)

def verify_password(plain_password: str, hashed_password: str) -> bool:
    """Vérifie si un mot de passe correspond au hachage"""
    return pwd_context.verify(plain_password, hashed_password)

def create_access_token(user_id: int):
    """Crée un token JWT d'accès"""
    expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    payload = {
        "sub": str(user_id),
        "exp": expire
    }
    return jwt.encode(payload, SECRET_KEY, algorithm=ALGORITHM)

ADMIN_TYPE = 1  
async def verify_admin(user_id: int = Depends(verify_token)):
    """
    Vérifie que l'utilisateur authentifié est un administrateur.
    À utiliser comme dépendance dans les routes réservées aux administrateurs.
    """
    user = get_user_by_id(user_id)
    
    if not user or user["type"] != ADMIN_TYPE:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès refusé: droits d'administrateur requis"
        )
    return user_id