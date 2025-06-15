#endpoints/classes.py



"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER  
Dernière date de mise à jour : 17/04/2025

Description : Gestion des routes API relatives aux opérations sur les classes
(récupération, création et suppression).
"""

from models.schemas import ClasseResponse, ClasseDelete, ClasseCreate
from core.auth import verify_token
from core.security import verify_admin
from db.requests.classes import get_all_classes, remove_classe, get_classe_by_nom, create_classe

from fastapi import APIRouter, Depends, HTTPException, status
from typing import List

router = APIRouter(tags=["classes"])


@router.get(
    "/",
    response_model=List[ClasseResponse],
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Liste des classes récupérée avec succès",
            "content": {
                "application/json": {
                    "example": [
                        {"nom": "3W03"},
                        {"nom": "3C01"}
                    ]
                }
            }
        },
        404: {
            "description": "Aucune classe trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Aucune classe trouvée"}
                }
            }
        }
    }
)
def get_classes():
    """
    Récupère la liste de toutes les classes.
    """
    classes = get_all_classes()
    if not classes:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune classe trouvée"
        )
    return classes


@router.delete(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Classe supprimée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Classe '1A' supprimée avec succès"}
                }
            }
        },
        404: {
            "description": "Classe non trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Classe non trouvée"}
                }
            }
        },
        500: {
            "description": "Erreur serveur lors de la suppression",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la suppression de la classe"}
                }
            }
        }
    }
)
def delete_classes(classe: ClasseDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime une classe existante.  
    Cette opération nécessite des droits administrateur.
    """
    existing_salle = get_classe_by_nom(classe.nom)
    if not existing_salle:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Classe non trouvée"
        )
    result = remove_classe(classe.nom)
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la classe"
        )
    return {"message": f"Classe '{classe.nom}' supprimée avec succès"}


@router.post(
    "/",
    response_model=dict,
    status_code=status.HTTP_201_CREATED,
    responses={
        201: {
            "description": "Classe créée avec succès",
            "content": {
                "application/json": {
                    "example": {
                        "message": "Classe créé avec succès",
                        "id": 3
                    }
                }
            }
        },
        400: {
            "description": "Une classe avec ce nom existe déjà",
            "content": {
                "application/json": {
                    "example": {"detail": "Une classe avec ce nom existe déjà"}
                }
            }
        },
        422: {
            "description": "Erreur de validation des données"
        }
    }
)
def add_classe(classe: ClasseCreate, user_id: int = Depends(verify_admin)):
    """
    Crée une nouvelle classe.  
    Cette opération nécessite des droits administrateur.
    """
    existing_classe = get_classe_by_nom(classe.nom)
    if existing_classe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une classe avec ce nom existe déjà"
        )
    classe_id = create_classe(nom=classe.nom)
    return {"message": "Classe créé avec succès", "id": classe_id}
