"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

/core/
    |----- security.py <-- Vous êtes ici
    |------auth.py
    |------utils.py

Description : ce programme fournit les fonctions avancées de sécurité liées à
l'authentification, notamment la génération de tokens JWT et la vérification
des privilèges administrateur. Ces fonctions sont utilisées pour sécuriser l'accès
aux différentes routes de l'API et gérer les autorisations utilisateur.
"""

from passlib.context import CryptContext
import jwt
from fastapi import Depends, HTTPException, status
from datetime import datetime, timedelta
from config import SECRET_KEY, ALGORITHM, ACCESS_TOKEN_EXPIRE_MINUTES
from db.requests.user import get_user_by_id
from core.auth import verify_token

# Configuration du contexte de cryptographie utilisant l'algorithme bcrypt
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

# Constante définissant le type d'utilisateur administrateur dans la base de données
ADMIN_TYPE = 1  

def create_access_token(user_id: int):
    # Calcul de la date d'expiration du token
    expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    
    # Préparation du payload du token avec l'identifiant utilisateur et la date d'expiration
    payload = {
        "sub": str(user_id),
        "exp": expire
    }
    
    # Encodage du token avec la clé secrète et l'algorithme configurés
    return jwt.encode(payload, SECRET_KEY, algorithm=ALGORITHM)


async def verify_admin(user_id: int = Depends(verify_token)):
    # Récupération des informations complètes de l'utilisateur
    user = get_user_by_id(user_id)
    
    # Vérification des droits administrateur
    if not user or user["type"] != ADMIN_TYPE:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès refusé: droits d'administrateur requis"
        )
    
    # Retour de l'identifiant utilisateur pour utilisation dans les routes protégées
    return user_id
