"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

/api/
    /endpoints
    |----- auth.py
    |------reservations.py
    |------users.py <-- Vous êtes ici
    |------creneaux.py


Description : ce programme permet de créer toutes les routes relatives à la gestion
des utilisateurs, notamment la création de nouveaux comptes utilisateurs avec vérification
des privilèges administrateur.
"""

from fastapi import APIRouter, HTTPException, status, Depends
from typing import List, Optional
from models.schemas import UserCreate, UserResponse
from models.user import create_user, get_user_by_login
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
        new_user = {
        "login": user.login,
        "passwd": user.password,
        "type": user.type,
        "nom": user.nom,
        "prenom": user.prenom
         }
    
    # Création de l'utilisateur en base de données
    user_id = create_user(new_user)
    
    return {"message": "Utilisateur créé avec succès", "id": user_id}
