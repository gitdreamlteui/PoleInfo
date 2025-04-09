"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 02/04/2025
"""

from models.schemas import SalleResponse, SalleDelete
from core.auth import verify_token
from models.salle import get_all_salles, get_salle_by_nom, delete_salle

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["salles"]
)

@router.get("/", response_model=List[SalleResponse])
def get_salles():

    salles = get_all_salles()
    
    if not salles:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune salles trouvée"
        )
    return salles


@router.delete("/", response_model=dict)
def delete_salle_endpoint(salle: SalleDelete, user_id: int = Depends(verify_token)):
    
    existing_salle = get_salle_by_nom(salle.numero)
    
    if not existing_salle:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Salle non trouvée"
        )
    
    result = delete_salle(salle.numero)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la salle"
        )
    
    return {"message": f"Salle '{salle.numero}' supprimée avec succès"}