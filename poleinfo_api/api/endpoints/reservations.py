"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 17/04/2025

Description : ce programme permet de créer toutes les routes relatives à la gestion
des réservations de salles, avec des fonctions pour créer et consulter les réservations.
"""

from models.schemas import ReservationCreate, ReservationResponse, ReservationDelete
from core.auth import verify_token

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional
from datetime import date

from models.reservation import get_all_reservations, get_reservations_by_salle_increase, get_reservations_by_salle, post_reservation, get_reservations_by_prof_increase, remove_reservation
from models.user import get_user_by_id

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["reservations"]
)

@router.post("/", response_model=dict)
def create_reservation(reservation: ReservationCreate, user_id: int = Depends(verify_token)):
    """
    Crée une nouvelle réservation de salle.
    
    L'utilisateur doit être authentifié pour effectuer cette opération.
    
    Args:
        reservation (ReservationCreate): Données de la réservation à créer
        user_id (int): ID de l'utilisateur authentifié
        
    Returns:
        dict: Message de confirmation et ID de la réservation créée
        
    Raises:
        HTTPException: 
            - Erreur 404 si l'utilisateur n'existe pas
            - Erreur 400 si la réservation ne peut pas être créée
    """
    user = get_user_by_id(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="Utilisateur non trouvé")
    
    username = user["login"]
    
    reservation_data = {
        "duree": reservation.duree,
        "date": reservation.date.isoformat(),
        "info": reservation.info if reservation.info else "",
        "numero_salle": reservation.numero_salle,
        "nom_matiere": reservation.nom_matiere,
        "heure_debut_creneau": str(reservation.heure_debut_creneau),
        "login_user": username,
        "nom_classe": reservation.nom_classe
    }
    
    result = post_reservation(**reservation_data)
    
    if result.get("status") == "success":
        return {
            "message": f"Réservation enregistrée par {username}.", 
            "id": result.get("id_reservation")
        }
    else:
        raise HTTPException(status_code=400, detail=result.get("message", "Erreur lors de la création de la réservation"))


@router.get("/", response_model=List[ReservationResponse])
def get_reservations(salle: str = Query(None, description="Numéro de la salle"),
                     croissant: bool = Query(None, description="Retourne les reservations dans l'ordre croissant"),
                     prof: str = Query(None, description="Retourne les réservations du professeur concerné par nom")):
    """
    Récupère la liste des réservations avec possibilité de filtrage.
    
    Args:
        salle (str, optional): Filtrer par numéro de salle
        croissant (bool, optional): Trier les résultats par ordre croissant
        prof (str, optional): Filtrer par nom de professeur
        
    Returns:
        List[ReservationResponse]: Liste des réservations correspondant aux critères
        
    Raises:
        HTTPException: Erreur 410 si aucune réservation n'est trouvée
    """
    if salle is not None and croissant == True:
        reservations = get_reservations_by_salle_increase(salle)
    elif prof is not None and croissant == True:
        reservations = get_reservations_by_prof_increase(prof)
    else:
        reservations = get_all_reservations()
    
    if not reservations:
        raise HTTPException(
            status_code=status.HTTP_410_GONE,
            detail="Aucune réservation trouvée"
        )
    return reservations

@router.delete("/", response_model=dict)
def delete_reservation(reservation: ReservationDelete, user_id: int = Depends(verify_token)):
    """
    Supprime une réservation en fonction des critères spécifiés dans le corps de la requête.
    Seul l'utilisateur qui a créé la réservation ou un admin peut la supprimer.
    
    Args:
        reservation (ReservationDelete): Critères de la réservation à supprimer
        user_id (int): ID de l'utilisateur authentifié
        
    Returns:
        dict: Message confirmant la suppression
        
    Raises:
        HTTPException: Erreur 400 si la suppression échoue
    """
    result = remove_reservation(
        user_id,
        reservation.date.isoformat(),
        reservation.numero_salle,
        reservation.heure_debut
    )
    
    if result.get("status") == "success":
        return {
            "message": "Réservation supprimée avec succès"
        }
    else:
        raise HTTPException(
            status_code=400, 
            detail=result.get("message", "Erreur lors de la suppression")
        )
