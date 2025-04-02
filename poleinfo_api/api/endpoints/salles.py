"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 02/04/2025

/api/
    /endpoints
    |----- auth.py
    |------reservations.py
    |------users.py
    |------creneaux.py  <-- Vous êtes ici
    |------salles.py 
"""

from models.schemas import SalleResponse
from core.auth import verify_token
from models.salle import get_all_salles

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["creneaux"]
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