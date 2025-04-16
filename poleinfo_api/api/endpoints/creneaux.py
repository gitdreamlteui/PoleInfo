"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025

Description : Gestion des routes API relatives aux créneaux horaires
(récupération, création et suppression).
"""

from models.schemas import CreneauResponse, CreneauDelete, CreneauCreate
from core.auth import verify_token
from core.security import verify_admin
from db.requests.creneau import get_all_creneaux, get_creneau_by_heure, remove_creneau, create_creneau

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["creneaux"]
)

@router.get("/", response_model=List[CreneauResponse])
def get_creneaux():
    """
    Récupère la liste de tous les créneaux horaires.
    
    Returns:
        List[CreneauResponse]: Liste des créneaux disponibles
        
    Raises:
        HTTPException: Erreur 404 si aucun créneau n'est trouvé
    """
    creneaux = get_all_creneaux()
    
    if not creneaux:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune réservation trouvée"
        )
    return creneaux

@router.delete("/", response_model=dict)
def delete_creneau(creneau: CreneauDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime un créneau horaire existant.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        creneau (CreneauDelete): Données du créneau à supprimer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation de la suppression
        
    Raises:
        HTTPException: 
            - Erreur 404 si le créneau n'existe pas
            - Erreur 500 en cas d'échec de la suppression
    """
    print(f"Créneau récupéré : {creneau.heure_debut}")

    existing_creneau = get_creneau_by_heure(creneau.heure_debut)
    print(f"Créneau récupéré : {existing_creneau}")
    
    if not existing_creneau:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Créneau non trouvé"
        )
    
    result = remove_creneau(creneau.heure_debut)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression du créneau"
        )
    
    return {"message": "Créneau supprimé avec succès"}

@router.post("/", response_model=dict)
def add_creneau(creneau: CreneauCreate, user_id: int = Depends(verify_admin)):
    """
    Crée un nouveau créneau horaire.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        creneau (CreneauCreate): Données du créneau à créer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation et ID du créneau créé
        
    Raises:
        HTTPException: Erreur 400 si un créneau avec la même heure de début existe déjà
    """
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
