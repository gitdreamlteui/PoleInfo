"""Logique d'authentification"""
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
import jwt
from jwt.exceptions import ExpiredSignatureError, InvalidTokenError  # Import correct des exceptions
from db.fake_db import fake_users_db
from config import SECRET_KEY, ALGORITHM

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

def verify_token(token: str = Depends(oauth2_scheme)):
    try:
        # Utilisation correcte de jwt.decode
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        username = payload.get("sub")

        if username not in [user["username"] for user in fake_users_db.values()]:
            raise HTTPException(status_code=401, detail="Utilisateur non autorisé")
        
        return username
    
    except ExpiredSignatureError:  # Utilisation directe de l'exception
        raise HTTPException(status_code=401, detail="Token expiré")
    except InvalidTokenError:  # Utilisation directe de l'exception
        raise HTTPException(status_code=401, detail="Token invalide")