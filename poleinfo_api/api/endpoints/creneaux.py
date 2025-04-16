"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import CreneauResponse, CreneauDelete, CreneauCreate
from core.auth import verify_token
from core.security import verify_admin
from models.creneau import get_all_creneaux, get_creneau_by_heure, delete_creneau, create_creneau

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
    print(f"Créneau récupéré : {creneau.heure_debut}")

    existing_creneau = get_creneau_by_heure(creneau.heure_debut)
    
    if not existing_creneau:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Créneau non trouvé"
        )
    
    result = delete_creneau(creneau.heure_debut)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression du créneau"
        )
    
    return {"message": "Créneau supprimé avec succès"}

@router.post("/", response_model=dict)
def add_creneau(creneau: CreneauCreate, user_id: int = Depends(verify_token)):
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