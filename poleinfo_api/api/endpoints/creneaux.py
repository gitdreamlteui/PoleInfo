#endpoints/creneaux.py



"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025

Description : Gestion des routes API relatives aux créneaux horaires
(récupération, création et suppression).
"""

from models.schemas import CreneauResponse, CreneauDelete, CreneauCreate
from core.auth import verify_token
from core.security import verify_admin
from db.requests.creneau import get_all_creneaux, get_creneau_by_heure, remove_creneau, create_creneau

from fastapi import APIRouter, Depends, HTTPException, status
from typing import List

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["creneaux"]
)

@router.get(
    "/",
    response_model=List[CreneauResponse],
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Liste des créneaux récupérée avec succès",
            "content": {
                "application/json": {
                    "example": [{"heure_debut": "08:00:00"}, {"heure_debut": "10:00:00"}]
                }
            }
        },
        404: {
            "description": "Aucun créneau trouvé",
            "content": {
                "application/json": {
                    "example": {"detail": "Aucun créneau trouvé"}
                }
            }
        }
    }
)
def get_creneaux():
    """
    Récupère la liste de tous les créneaux horaires.
    """
    creneaux = get_all_creneaux()
    
    if not creneaux:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucun créneau trouvé"
        )
    return creneaux


@router.delete(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Créneau supprimé avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Créneau supprimé avec succès"}
                }
            }
        },
        404: {
            "description": "Créneau non trouvé",
            "content": {
                "application/json": {
                    "example": {"detail": "Créneau non trouvé"}
                }
            }
        },
        500: {
            "description": "Erreur serveur lors de la suppression",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la suppression du créneau"}
                }
            }
        }
    }
)
def delete_creneau(creneau: CreneauDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime un créneau horaire existant.
    Cette opération nécessite des droits administrateur.
    """
    existing_creneau = get_creneau_by_heure(creneau.heure_debut)
    
    if not existing_creneau:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Créneau non trouvé"
        )
    
    result = remove_creneau(creneau.heure_debut)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression du créneau"
        )
    
    return {"message": "Créneau supprimé avec succès"}


@router.post(
    "/",
    response_model=dict,
    status_code=status.HTTP_201_CREATED,
    responses={
        201: {
            "description": "Créneau créé avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Créneau créé avec succès", "id": 4}
                }
            }
        },
        400: {
            "description": "Créneau déjà existant",
            "content": {
                "application/json": {
                    "example": {"detail": "Un créneau avec ces heures existe déjà"}
                }
            }
        }
    }
)
def add_creneau(creneau: CreneauCreate, user_id: int = Depends(verify_admin)):
    """
    Crée un nouveau créneau horaire.
    Cette opération nécessite des droits administrateur.
    """
    existing_creneau = get_creneau_by_heure(creneau.heure_debut)
    if existing_creneau:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Un créneau avec ces heures existe déjà"
        )
    
    creneau_id = create_creneau(
        heure_debut=creneau.heure_debut
    )
    
    return {"message": "Créneau créé avec succès", "id": creneau_id}
