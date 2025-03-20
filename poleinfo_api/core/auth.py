"""Fonctions d'authentification et de vérification de token"""
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import JWTError, jwt
from typing import Optional

from config import SECRET_KEY, ALGORITHM
from models.user import get_user_by_id

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

def verify_token(token: str = Depends(oauth2_scheme)) -> int:
    """Vérifie la validité du token JWT et retourne l'identifiant de l'utilisateur"""
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Credentials invalides",
        headers={"WWW-Authenticate": "Bearer"},
    )
    
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id_str: str = payload.get("sub")
        
        if user_id_str is None:
            raise credentials_exception
            
        user_id = int(user_id_str)  # Convertir en entier
        
        # Vérifier si l'utilisateur existe dans la base de données
        user = get_user_by_id(user_id)
        if user is None:
            raise credentials_exception
        
        return user_id
    except (JWTError, ValueError):
        raise credentials_exception
