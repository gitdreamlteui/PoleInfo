"""
API Pôle Info
--------------

Auteur : Ethan CLEMENT
Dernière date de mise à jour : 3/04/2025

/api/
    /endpoints
    |----- auth.py
    |------reservations.py
    |------users.py
    |------creneaux.py
    |------matieres.py  <-- Vous êtes ici
"""

from models.schemas import ClasseResponse
from core.auth import verify_token
from models.classe import get_all_classes

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
    return matieres