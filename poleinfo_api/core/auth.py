#core/auth.py

"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

Description : ce programme contient les fonctions essentielles pour l'authentification
et la vérification des tokens JWT utilisés dans l'ensemble de l'API. Il permet de sécuriser
les routes en vérifiant la validité des tokens fournis par les utilisateurs.
"""

from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import JWTError, jwt
from typing import Optional

from config import SECRET_KEY, ALGORITHM
from db.requests.user import get_user_by_id

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")


"""
Fonction qui vérifie la validité d'un token JWT et extrait l'identifiant de l'utilisateur.

Cette fonction est utilisée comme dépendance dans les routes protégées pour:
1. Extraire le token JWT de l'en-tête de la requête
2. Décoder le token avec la clé secrète et l'algorithme configurés
3. Extraire l'identifiant utilisateur (sub) du payload
4. Vérifier que l'utilisateur existe toujours dans la base de données

En cas d'échec à n'importe quelle étape (token invalide, expiré, utilisateur inexistant),
une exception HTTP 401 est levée avec les en-têtes appropriés.

Retourne l'identifiant de l'utilisateur authentifié si tout est valide.

Utilise les fonctions jwt.decode pour le décodage sécurisé et get_user_by_id pour
la vérification en base de données.
"""
def verify_token(token: str = Depends(oauth2_scheme)) -> int:
    # Préparation de l'exception pour les erreurs d'authentification
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
        
        user_id = int(user_id_str)
        user = get_user_by_id(user_id)

        if user is None:
            raise credentials_exception
        return user_id
        
    except (JWTError, ValueError):
        raise credentials_exception
