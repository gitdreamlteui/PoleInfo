"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import CreneauResponse, CreneauDelete
from core.auth import verify_token
from models.creneau import get_all_creneaux, get_creneau_by_heure, delete_creneau

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["creneaux"]
)

@router.get("/", response_model=List[CreneauResponse])
def get_creneaux():

    creneaux = get_all_creneaux()
    
    if not creneaux:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune réservation trouvée"
        )
    return creneaux

@router.delete("/", response_model=dict)
def delete_creneau(creneau: CreneauDelete, user_id: int = Depends(verify_token)):
    
    existing_matiere = get_creneau_by_heure(creneau.heure_debut)
    
    if not existing_matiere:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Créneau non trouvé"
        )
    
    result = delete_creneau(creneau.nom)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression du créneau"
        )
    
    return {"message": f"Créneau '{creneau.heure_debut}' supprimé avec succès"}