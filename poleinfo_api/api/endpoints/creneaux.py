"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

/api/
    /endpoints
    |----- auth.py
    |------reservations.py
    |------users.py
    |------creneaux.py  <-- Vous êtes ici
"""

from models.schemas import CreneauResponse
from core.auth import verify_token
from models.creneau import get_all_creneaux

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