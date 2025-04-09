"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import ClasseResponse
from core.auth import verify_token
from models.classes import get_all_classes

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