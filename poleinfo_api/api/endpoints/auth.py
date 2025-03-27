"""Routes d'authentification"""
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm

from core.security import create_access_token
from core.auth import verify_token
from models.schemas import Token
from models.user import authenticate_user

router = APIRouter(tags=["authentication"])

@router.post("/token", response_model=Token)
def login(form_data: OAuth2PasswordRequestForm = Depends()):
    """Authentification des utilisateurs existants et génération de token JWT"""
    user = authenticate_user(form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Identifiants incorrects"
        )
    type_user = user["type"]
    token = create_access_token(user["id_user"])
    return {"access_token": token, "token_type": "bearer", "user_type": type_user}

@router.get("/verify-token")
def verify_token_endpoint(user_id: int = Depends(verify_token)):
    """Vérifie la validité d'un token JWT"""
    return {"valid": True, "user_id": user_id}
