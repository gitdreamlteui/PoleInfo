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
    |------creneaux.py
    |------matieres.py  <-- Vous êtes ici
"""

from models.schemas import MatiereResponse
from core.auth import verify_token
from models.matiere import get_all_matieres

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

router = APIRouter(
    tags=["matieres"]
)

@router.get("/", response_model=List[MatiereResponse])
def get_matieres():

    matieres = get_all_matieres()
    
    if not matieres:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune matière trouvée"
        )
    return matieres