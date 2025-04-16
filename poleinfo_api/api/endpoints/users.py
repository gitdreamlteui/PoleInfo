"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

Description : ce programme permet de créer toutes les routes relatives à la gestion
des utilisateurs, notamment la création de nouveaux comptes utilisateurs avec vérification
des privilèges administrateur.
"""

from fastapi import APIRouter, HTTPException, status, Depends
from typing import List, Optional
from models.schemas import UserCreate, UserResponse, UserDelete
from models.user import create_user, get_user_by_login, get_all_users, delete_user_by_login
from core.auth import verify_token
from core.security import verify_admin

router = APIRouter(
    tags=["utilisateurs"]
)

"""
Fonction qui permet d'ajouter un nouvel utilisateur dans le système.
Cette route est protégée et nécessite des privilèges administrateur.

Elle effectue d'abord une vérification pour s'assurer que le login n'existe pas déjà.
En cas de doublon, une exception HTTP 400 est levée.

Ensuite, elle prépare les données de l'utilisateur pour l'insertion en base de données
et appelle la fonction create_user pour réaliser l'opération.

La fonction retourne un message de confirmation avec l'identifiant de l'utilisateur créé.

Utilise les méthodes verify_admin pour la sécurité et get_user_by_login pour la validation.
"""
@router.post("/", response_model=dict)
def add_user(user: UserCreate, admin_id: int = Depends(verify_admin)):
    existing_user = get_user_by_login(user.login)
    if existing_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Un utilisateur avec ce login existe déjà"
        )
    
    user_id = create_user(
        
        login=user.login,
        password=user.password,
        type=user.type,
        nom=user.nom,
        prenom=user.prenom
    )
    
    return {"message": "Utilisateur créé avec succès", "id": user_id}

@router.delete("/", response_model=dict)
def delete_users(user: UserDelete, user_id: int = Depends(verify_token)):
    
    existing_user = get_user_by_login(user.login)
    
    if not existing_user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    result = delete_user_by_login(user.login)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de l'utilisateur"
        )
    
    return {"message": f"Utilisateur '{user.nom}' supprimé avec succès"}


@router.get("/", response_model=List[UserResponse])
def get_users(): #admin_id: int = Depends(verify_admin)
    users = get_all_users()
        
    if len(users) == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucun utilisateur trouvé"
        )
    
    return users
