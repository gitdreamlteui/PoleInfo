"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 17/04/2025

Description : ce programme permet de créer toutes les routes relatives à la gestion
des réservations de salles, avec des fonctions pour créer et consulter les réservations.
"""

from models.schemas import ReservationCreate, ReservationResponse, ReservationDelete, ReservationUpdate
from core.auth import verify_token

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional
from datetime import date

from db.requests.reservation import get_all_reservations, get_reservations_by_salle_increase, get_reservations_by_salle, post_reservation, get_reservations_by_prof_increase, remove_reservation, remove_reservation_by_id, update_reservation, get_reservation_by_id
from db.requests.user import get_user_by_id
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
            - Erreur 409 si les classes sont déjà réservées
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
    elif "déjà réservée" in result.get("message", ""):
        # Conflit de réservation pour une classe
        raise HTTPException(status_code=409, detail=result.get("message"))
    else:
        raise HTTPException(status_code=400, detail=result.get("message", "Erreur lors de la création de la réservation"))



@router.get("/", response_model=List[ReservationResponse])
def get_reservations(salle: str = Query(None, description="Numéro de la salle"),
                     croissant: bool = Query(None, description="Retourne les réservations dans l'ordre croissant"),
                     prof: str = Query(None, description="Retourne les réservations du professeur concerné par nom"),
                     reservation_id: int = Query(None, description="Retourne les informations d'une certaine réservation par son ID")):
    """
    Récupère la liste des réservations avec possibilité de filtrage.
    """
    if reservation_id is not None:
        reservation = get_reservation_by_id(reservation_id)
        if not reservation:
            raise HTTPException(
                status_code=status.HTTP_410_GONE,
                detail="Aucune réservation trouvée pour cet ID"
            )
        return [reservation]

    elif salle and croissant:
        reservations = get_reservations_by_salle_increase(salle)
    elif prof and croissant:
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
    Supprime une réservation soit par son ID, soit par les critères date/salle/heure.
    Seul l'utilisateur qui a créé la réservation ou un admin peut la supprimer.
    
    Args:
        reservation (ReservationDelete): ID de la réservation ou critères de recherche
        user_id (int): ID de l'utilisateur authentifié
        
    Returns:
        dict: Message confirmant la suppression
        
    Raises:
        HTTPException: Erreur 400 si la suppression échoue ou 422 si les paramètres sont insuffisants
    """
    # Suppression par ID
    if reservation.id_reservation is not None:
        result = remove_reservation_by_id(user_id, reservation.id_reservation)

    elif all([reservation.date, reservation.numero_salle, reservation.heure_debut]):
        result = remove_reservation(
            user_id,
            reservation.date.isoformat(),
            reservation.numero_salle,
            reservation.heure_debut
        )
    else:
        raise HTTPException(
            status_code=422, 
            detail="Paramètres insuffisants. Fournir soit id_reservation, soit date + numero_salle + heure_debut"
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

@router.put("/", response_model=dict)
def update_reservation_endpoint(reservation: ReservationUpdate, user_id: int = Depends(verify_token)):
    """
    Met à jour une réservation existante.
    
    L'utilisateur doit être authentifié pour effectuer cette opération.
    
    Args:
        reservation (ReservationUpdate): Données de la réservation à mettre à jour
        user_id (int): ID de l'utilisateur authentifié
        
    Returns:
        dict: Message de confirmation
        
    Raises:
        HTTPException: Erreur 400 si la mise à jour échoue
    """
    result = update_reservation(
        id_reservation=reservation.id_reservation,
        duree=reservation.duree,
        date=reservation.date,
        info=reservation.info,
        numero_salle=reservation.numero_salle,
        nom_matiere=reservation.nom_matiere,
        heure_debut_creneau=reservation.heure_debut_creneau,
        nom_classe=reservation.nom_classe
    )

    if result.get("status") == "success":
        return {"message": result["message"]}
    else:
        raise HTTPException(status_code=400, detail=result.get("message", "Erreur lors de la mise à jour"))