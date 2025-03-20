"""Routes pour la gestion des utilisateurs"""
from fastapi import APIRouter, HTTPException, status
from db.fake_db import fake_users_db
from models.schemas import UserCreate, UserResponse
from core.security import hash_password

router = APIRouter()

@router.post("/", response_model=UserResponse)
def create_user(user: UserCreate):
    """Créer un nouvel utilisateur"""
    user_id = len(fake_users_db) + 1
    hashed_password = hash_password(user.password)
    
    fake_users_db[user_id] = {"username": user.username, "password": hashed_password}
    return {"id": user_id, "username": user.username}

@router.get("/", response_model=list[UserResponse])
def get_users():
    """Récupérer la liste des utilisateurs"""
    if not fake_users_db:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, 
            detail="Aucun utilisateur"
        )
    return [{"id": user_id, "username": data["username"]} for user_id, data in fake_users_db.items()]

@router.get("/{user_id}", response_model=UserResponse)
def get_user(user_id: int):
    """Récupérer un utilisateur par son ID"""
    user = fake_users_db.get(user_id)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, 
            detail="Utilisateur non trouvé"
        )
    
    return {"id": user_id, "username": user["username"]}

