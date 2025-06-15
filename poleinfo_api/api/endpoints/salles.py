#endpoints/salles.py


"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 02/04/2025

Description : Gestion des routes API relatives aux salles de classe
(récupération, création et suppression).
"""

from models.schemas import SalleResponse, SalleDelete, SalleCreate
from core.auth import verify_token
from core.security import verify_admin

from db.requests.salle import get_all_salles, get_salle_by_numero, remove_salle, create_salle

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["salles"]
)

@router.get(
    "/",
    response_model=List[SalleResponse],
    responses={
        200: {
            "description": "Liste des salles récupérée avec succès",
            "content": {
                "application/json": {
                    "example": [
                        {"numero": "A101", "capacite": 30, "type": "Informatique"},
                        {"numero": "B202", "capacite": 25, "type": "Labo"}
                    ]
                }
            }
        },
        404: {
            "description": "Aucune salle trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Aucune salles trouvée"}
                }
            }
        }
    }
)
def get_salles():
    salles = get_all_salles()
    if not salles:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune salles trouvée"
        )
    return salles


@router.delete(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Salle supprimée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Salle 'B202' supprimée avec succès"}
                }
            }
        },
        404: {
            "description": "Salle non trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Salle non trouvée"}
                }
            }
        },
        500: {
            "description": "Erreur lors de la suppression de la salle",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la suppression de la salle"}
                }
            }
        }
    }
)
def delete_salle(salle: SalleDelete, user_id: int = Depends(verify_admin)):
    existing_salle = get_salle_by_numero(salle.numero)
    if not existing_salle:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Salle non trouvée"
        )
    
    result = remove_salle(salle.numero)
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la salle"
        )
    
    return {"message": f"Salle '{salle.numero}' supprimée avec succès"}


@router.post(
    "/",
    response_model=dict,
    status_code=status.HTTP_201_CREATED,
    responses={
        201: {
            "description": "Salle créée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Salle créé avec succès", "id": 12}
                }
            }
        },
        400: {
            "description": "Une salle avec ce numéro existe déjà",
            "content": {
                "application/json": {
                    "example": {"detail": "Une salle avec ce numéro existe déjà"}
                }
            }
        }
    }
)
def add_salle(salle: SalleCreate, user_id: int = Depends(verify_admin)):
    existing_salle = get_salle_by_numero(salle.numero)
    if existing_salle:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une salle avec ce numéro existe déjà"
        )
    
    salle_id = create_salle(
        numero=salle.numero,
        capacite=salle.capacite,
        type=salle.type
    )
    
    return {"message": "Salle créé avec succès", "id": salle_id}
