"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025
"""

from models.schemas import MatiereResponse, MatiereDelete, MatiereCreate
from core.auth import verify_token
from models.matiere import get_all_matieres, remove_matiere, get_matiere_by_nom, create_matiere

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

@router.delete("/", response_model=dict)
def delete_matieres(matiere: MatiereDelete, user_id: int = Depends(verify_token)):
    
    existing_matiere = get_matiere_by_nom(matiere.nom)
    
    if not existing_matiere:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Matière non trouvée"
        )
    
    result = remove_matiere(matiere.nom)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la matière"
        )
    
    return {"message": f"Matière '{matiere.nom}' supprimée avec succès"}


@router.post("/", response_model=dict)
def add_matiere(matiere: MatiereCreate, user_id: int = Depends(verify_token)):
    existing_matiere = get_matiere_by_nom(matiere.nom)
    if existing_matiere:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une matière avec ce nom existe déjà"
        )
    
    creneau_id = create_matiere(
        nom=matiere.nom
    )
    
    return {"message": "Matière créé avec succès", "id": creneau_id}