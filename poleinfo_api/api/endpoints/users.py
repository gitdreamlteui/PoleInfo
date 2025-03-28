"""Routes pour la gestion des utilisateurs"""
from fastapi import APIRouter, HTTPException, status, Depends
from typing import List, Optional
from models.schemas import UserCreate, UserResponse
from models.user import create_user, get_user_by_login
from core.auth import verify_token
from core.security import verify_admin

router = APIRouter(
    tags=["utilisateurs"]
)

@router.post("/", response_model=dict)
def add_user(user: UserCreate, admin_id: int = Depends(verify_admin)):
    """Créer un nouvel utilisateur (protégée par authentification admin)"""
    
    existing_user = get_user_by_login(user.login)
    if existing_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Un utilisateur avec ce login existe déjà"
        )
        
    new_user = {
        "login": user.login,
        "passwd": user.password,
        "type": user.type,
        "nom": user.nom,
        "prenom": user.prenom
    }
    
    user_id = create_user(new_user)
    
    return {"message": "Utilisateur créé avec succès", "id": user_id}
