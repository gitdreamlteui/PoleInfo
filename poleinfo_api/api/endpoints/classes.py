"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import ClasseResponse, ClasseDelete, ClasseCreate
from core.auth import verify_token
from core.security import verify_admin
from models.classes import get_all_classes, remove_classe, get_classe_by_nom, create_classe

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["classes"]
)

@router.get("/", response_model=List[ClasseResponse])
def get_classes():

    classes = get_all_classes()
    
    if not classes:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune classe trouvée"
        )
    return classes

@router.delete("/", response_model=dict)
def delete_classes(classe: ClasseDelete, user_id: int = Depends(verify_admin)):
    
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

@router.post("/", response_model=dict)
def add_classe(classe: ClasseCreate, user_id: int = Depends(verify_admin)):
    existing_classe = get_classe_by_nom(classe.nom)
    if existing_classe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une classe avec ce nom existe déjà"
        )
    
    classe_id = create_classe(
        nom=classe.nom
    )
    
    return {"message": "Classe créé avec succès", "id": classe_id}