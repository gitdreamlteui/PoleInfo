"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 14/04/2025

Description : ce programme permet de créer toute les routes relatives aux authentifications 
via un token. 
"""

from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm

from core.security import create_access_token
from core.auth import verify_token
from models.schemas import Token
from models.user import authenticate_user

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(tags=["authentification"])


@router.post("/token", response_model=Token)
def login(form_data: OAuth2PasswordRequestForm = Depends()):
    """
    Authentifie un utilisateur et génère un token JWT.
    
    Cette fonction vérifie les identifiants de l'utilisateur,
    génère un token JWT si l'authentification réussit, et renvoie le token 
    avec le type d'utilisateur et son nom.
    
    Args:
        form_data (OAuth2PasswordRequestForm): Formulaire contenant les identifiants (username, password)
        
    Returns:
        Token: Un objet contenant le token d'accès, son type, le type d'utilisateur et son nom
        
    Raises:
        HTTPException: Erreur 400 si les identifiants sont incorrects
    """
    user = authenticate_user(form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Identifiants incorrects"
        )
    
    type_user = user["type"]
    user_login = user["login"]
    user_name = user["nom"]

    token = create_access_token(user["id_user"])
    return {"access_token": token, "token_type": "bearer", "user_type": type_user, "user_name": user_name}


@router.get("/verify-token")
def verify_token_endpoint(user_id: int = Depends(verify_token)):
    """
    Vérifie la validité d'un token JWT.
    
    Utilise la dépendance verify_token pour valider le token
    et récupérer l'ID de l'utilisateur associé.
    
    Args:
        user_id (int): ID de l'utilisateur extrait du token par verify_token
        
    Returns:
        dict: Un objet JSON indiquant que le token est valide et l'ID de l'utilisateur
    """
    return {"valid": True, "user_id": user_id}
