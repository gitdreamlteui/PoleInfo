#endpoints/matieres.py



"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 17/04/2025

Description : Gestion des routes API relatives aux matières scolaires
(récupération, création et suppression).
"""

from models.schemas import MatiereResponse, MatiereDelete, MatiereCreate
from core.auth import verify_token
from core.security import verify_admin
from db.requests.matiere import get_all_matieres, remove_matiere, get_matiere_by_nom, create_matiere

from fastapi import APIRouter, Depends, HTTPException, status
from typing import List

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["matieres"]
)

@router.get(
    "/",
    response_model=List[MatiereResponse],
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Liste des matières récupérée avec succès",
            "content": {
                "application/json": {
                    "example": [{"nom": "Mathématiques"}, {"nom": "Physique"}]
                }
            }
        },
        404: {
            "description": "Aucune matière trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Aucune matière trouvée"}
                }
            }
        }
    }
)
def get_matieres():
    """
    Récupère la liste de toutes les matières.
    """
    matieres = get_all_matieres()
    
    if not matieres:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune matière trouvée"
        )
    return matieres


@router.delete(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Matière supprimée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Matière 'Mathématiques' supprimée avec succès"}
                }
            }
        },
        404: {
            "description": "Matière non trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Matière non trouvée"}
                }
            }
        },
        500: {
            "description": "Erreur serveur lors de la suppression",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la suppression de la matière"}
                }
            }
        }
    }
)
def delete_matieres(matiere: MatiereDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime une matière existante.
    Cette opération nécessite des droits administrateur.
    """
    existing_matiere = get_matiere_by_nom(matiere.nom)
    
    if not existing_matiere:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Matière non trouvée"
        )
    
    result = remove_matiere(matiere.nom)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la matière"
        )
    
    return {"message": f"Matière '{matiere.nom}' supprimée avec succès"}


@router.post(
    "/",
    response_model=dict,
    status_code=status.HTTP_201_CREATED,
    responses={
        201: {
            "description": "Matière créée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Matière créée avec succès", "id": 7}
                }
            }
        },
        400: {
            "description": "Matière déjà existante",
            "content": {
                "application/json": {
                    "example": {"detail": "Une matière avec ce nom existe déjà"}
                }
            }
        }
    }
)
def add_matiere(matiere: MatiereCreate, user_id: int = Depends(verify_admin)):
    """
    Crée une nouvelle matière.
    Cette opération nécessite des droits administrateur.
    """
    existing_matiere = get_matiere_by_nom(matiere.nom)
    if existing_matiere:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une matière avec ce nom existe déjà"
        )
    
    matiere_id = create_matiere(nom=matiere.nom)
    
    return {"message": "Matière créée avec succès", "id": matiere_id}
