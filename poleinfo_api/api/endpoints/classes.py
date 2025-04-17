"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import ClasseResponse, ClasseDelete
from core.auth import verify_token
from models.classes import get_all_classes, remove_classe

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
def delete_classes(classe: ClasseDelete, user_id: int = Depends(verify_token)):
    
    existing_salle = get_classes(classe.nom)
    
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