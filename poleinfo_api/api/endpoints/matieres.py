"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 17/04/2025

Description : Gestion des routes API relatives aux matières scolaires
(récupération, création et suppression).
"""

from models.schemas import MatiereResponse, MatiereDelete, MatiereCreate
from core.auth import verify_token
from core.security import verify_admin
from db.requests.matiere import get_all_matieres, remove_matiere, get_matiere_by_nom, create_matiere

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["matieres"]
)

@router.get("/", response_model=List[MatiereResponse])
def get_matieres():
    """
    Récupère la liste de toutes les matières scolaires.
    
    Returns:
        List[MatiereResponse]: Liste des matières disponibles
        
    Raises:
        HTTPException: Erreur 404 si aucune matière n'est trouvée
    """
    matieres = get_all_matieres()
    
    if not matieres:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune matière trouvée"
        )
    return matieres

@router.delete("/", response_model=dict)
def delete_matieres(matiere: MatiereDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime une matière existante.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        matiere (MatiereDelete): Données de la matière à supprimer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation de la suppression
        
    Raises:
        HTTPException: 
            - Erreur 404 si la matière n'existe pas
            - Erreur 500 en cas d'échec de la suppression
    """
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
def add_matiere(matiere: MatiereCreate, user_id: int = Depends(verify_admin)):
    """
    Crée une nouvelle matière.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        matiere (MatiereCreate): Données de la matière à créer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation et ID de la matière créée
        
    Raises:
        HTTPException: Erreur 400 si une matière avec le même nom existe déjà
    """
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
