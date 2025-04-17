"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 09/04/2025

Description : Gestion des routes API relatives aux opérations sur les classes
(récupération, création et suppression).
"""

from models.schemas import ClasseResponse, ClasseDelete, ClasseCreate
from core.auth import verify_token
from core.security import verify_admin
from models.classes import get_all_classes, remove_classe, get_classe_by_nom, create_classe

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["classes"]
)

@router.get("/", response_model=List[ClasseResponse])
def get_classes():
    """
    Récupère la liste de toutes les classes.
    
    Returns:
        List[ClasseResponse]: Liste des classes disponibles
        
    Raises:
        HTTPException: Erreur 404 si aucune classe n'est trouvée
    """
    classes = get_all_classes()
    
    if not classes:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune classe trouvée"
        )
    return classes

@router.delete("/", response_model=dict)
def delete_classes(classe: ClasseDelete, user_id: int = Depends(verify_admin)):
    """
    Supprime une classe existante.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        classe (ClasseDelete): Données de la classe à supprimer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation de la suppression
        
    Raises:
        HTTPException: 
            - Erreur 404 si la classe n'existe pas
            - Erreur 500 en cas d'échec de la suppression
    """
    existing_salle = get_classe_by_nom(classe.nom)
    
    if not existing_salle:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Classe non trouvée"
        )
    
    result = remove_classe(classe.nom)
    
    if not result:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Erreur lors de la suppression de la classe"
        )
    
    return {"message": f"Classe '{classe.nom}' supprimée avec succès"}

@router.post("/", response_model=dict)
def add_classe(classe: ClasseCreate, user_id: int = Depends(verify_admin)):
    """
    Crée une nouvelle classe.
    
    Cette opération nécessite des droits administrateur.
    
    Args:
        classe (ClasseCreate): Données de la classe à créer
        user_id (int): ID de l'utilisateur (vérifié comme administrateur)
        
    Returns:
        dict: Message de confirmation et ID de la classe créée
        
    Raises:
        HTTPException: Erreur 400 si une classe avec le même nom existe déjà
    """
    existing_classe = get_classe_by_nom(classe.nom)
    if existing_classe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Une classe avec ce nom existe déjà"
        )
    
    classe_id = create_classe(
        nom=classe.nom
    )
    
    return {"message": "Classe créé avec succès", "id": classe_id}
