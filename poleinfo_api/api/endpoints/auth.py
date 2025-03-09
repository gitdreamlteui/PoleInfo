"""Routes d'authentification"""
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from db.fake_db import fake_users_db

from core.security import verify_password, create_access_token
from core.auth import verify_token
from models.schemas import Token

router = APIRouter(tags=["authentication"])

@router.post("/token", response_model=Token)
def login(form_data: OAuth2PasswordRequestForm = Depends()):
    """Authentification des utilisateurs existants et génération de token JWT"""
    user = next((u for u in fake_users_db.values() if u["username"] == form_data.username), None)
    
    if not user or not verify_password(form_data.password, user["password"]):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Identifiants incorrects"
        )
    
    token = create_access_token(user["username"])
    return {"access_token": token, "token_type": "bearer"}

@router.get("/verify-token", tags=["authentication"])
def verify_token_endpoint(username: str = Depends(verify_token)):
    """Vérifie la validité d'un token JWT"""
    return {"valid": True, "user": username}

