"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

/api/
    /endpoints
    |----- auth.py <-- Vous êtes ici
    |------reservations.py
    |------users.py
    |------creneaux.py


Description : ce programme permet de créer toute les routes relatives aux authentifications 
via un token. 
"""

from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm

from core.security import create_access_token
from core.auth import verify_token
from models.schemas import Token
from models.user import authenticate_user

router = APIRouter(tags=["authentication"])


"""
Fonction d'authentification qui vérifie les identifiants de l'utilisateur,
génère un token JWT si l'authentification réussit, et renvoie le token 
avec le type d'utilisateur et son nom.  

En cas d'échec d'authentification, une exception HTTP 400 est levée.

Avec les méthodes create_access_token, et authenticate_user
"""
@router.post("/token", response_model=Token)
def login(form_data: OAuth2PasswordRequestForm = Depends()):
    user = authenticate_user(form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Identifiants incorrects"
        )
    type_user = user["type"]
    user_name = user["login"]
    token = create_access_token(user["id_user"])
    return {"access_token": token, "token_type": "bearer", "user_type": type_user, "user_name": user_name}


"""
Vérifie la validité d'un token JWT en utilisant verify_token
"""
@router.get("/verify-token")
def verify_token_endpoint(user_id: int = Depends(verify_token)):
    return {"valid": True, "user_id": user_id}
