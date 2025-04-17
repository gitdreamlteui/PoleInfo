"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 02/04/2025

Description : Gestion des routes API relatives aux salles de classe
(récupération, création et suppression).
"""

from models.schemas import SalleResponse, SalleDelete, SalleCreate
from core.auth import verify_token
from core.security import verify_admin

from models.salle import get_all_salles, get_salle_by_numero, remove_salle, create_salle

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["salles"]
)

@router.get("/", response_model=List[SalleResponse])
def get_salles():
    """
    Récupère la liste de toutes les salles.
    
    Returns:
        List[SalleResponse]: Liste des salles disponibles
        
    Raises:
        HTTPException: Erreur 404 si aucune salle n'est trouvée
    """
    salles = get_all_salles()
    
    if not salles:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune salles trouvée"
        )
    return salles


@router.delete("/", response_model=dict)
def delete_salle(salle: SalleDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime une salle existante.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        salle (SalleDelete): Données de la salle à supprimer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation de la suppression
        
    Raises:
        HTTPException: 
            - Erreur 404 si la salle n'existe pas
            - Erreur 500 en cas d'échec de la suppression
    """
    existing_salle = get_salle_by_numero(salle.numero)
    
    if not existing_salle:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Salle non trouvée"
        )
    
    result = remove_salle(salle.numero)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la salle"
        )
    
    return {"message": f"Salle '{salle.numero}' supprimée avec succès"}

@router.post("/", response_model=dict)
def add_salle(salle: SalleCreate, user_id: int = Depends(verify_admin)):
    """
    Crée une nouvelle salle.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        salle (SalleCreate): Données de la salle à créer (numéro, capacité, type)
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation et ID de la salle créée
        
    Raises:
        HTTPException: Erreur 400 si une salle avec le même numéro existe déjà
    """
    existing_salle = get_salle_by_numero(salle.numero)
    if existing_salle:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une salle avec ce numéro existe déjà"
        )
    
    salle_id = create_salle(
        numero=salle.numero,
        capacite=salle.capacite,
        type=salle.type
    )
    
    return {"message": "Salle créé avec succès", "id": salle_id}
