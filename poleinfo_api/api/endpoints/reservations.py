"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 17/04/2025

Description : Ce programme permet de créer toutes les routes relatives à la gestion
des réservations de salles, avec des fonctions pour créer et consulter les réservations.
"""

from models.schemas import ReservationCreate, ReservationResponse, ReservationDelete, ReservationUpdate
from core.auth import verify_token

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional
from datetime import date

from db.requests.reservation import (
    get_all_reservations, get_reservations_by_salle_increase,
    get_reservations_by_salle, post_reservation, get_reservations_by_prof_increase,
    remove_reservation, remove_reservation_by_id, update_reservation, get_reservation_by_id
)
from db.requests.user import get_user_by_id

# Définition du router avec le tag pour la documentation Swagger
router = APIRouter(
    tags=["reservations"]
)

@router.post(
    "/",
    response_model=dict,
    status_code=status.HTTP_201_CREATED,
    responses={
        201: {
            "description": "Réservation créée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Réservation enregistrée par egauthier.", "id": 42}
                }
            }
        },
        400: {
            "description": "Erreur lors de la création de la réservation",
            "content": {
                "application/json": {
                    "example": {"detail": "Cette salle est déjà réservée pour cet horaire"}
                }
            }
        },
        404: {
            "description": "Utilisateur non trouvé",
            "content": {
                "application/json": {
                    "example": {"detail": "Utilisateur non trouvé"}
                }
            }
        }
    }
)
def create_reservation(reservation: ReservationCreate, user_id: int = Depends(verify_token)):
    user = get_user_by_id(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="Utilisateur non trouvé")
    
    username = user["login"]

    result = post_reservation(
        duree=reservation.duree,
        date=reservation.date,
        info=reservation.info or "",
        numero_salle=reservation.numero_salle,
        nom_matiere=reservation.nom_matiere,
        heure_debut_creneau=reservation.heure_debut_creneau,
        login_user=username,
        nom_classe=reservation.nom_classe
    )

    if result.get("status") == "success":
        return {
            "message": f"Réservation enregistrée par {username}.",
            "id": result.get("id_reservation")
        }

    error_mapping = {
        "error_reserv": "Cette salle est déjà réservée pour cet horaire",
        "error_overtime": "L'horaire ne peut pas dépasser 17h25",
        "error_overtime_midi": "L'horaire ne peut pas dépasser 12h35"
    }

    error_message = result.get("message") or error_mapping.get(result.get("status"), "Erreur lors de la création de la réservation, veuillez consulter un administrateur.")
    
    raise HTTPException(status_code=400, detail=error_message)


@router.get(
    "/",
    response_model=List[ReservationResponse],
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Liste des réservations récupérée avec succès",
        },
        410: {
            "description": "Aucune réservation trouvée",
            "content": {
                "application/json": {
                    "example": {"detail": "Aucune réservation trouvée"}
                }
            }
        }
    }
)
def get_reservations(
    salle: str = Query(None, description="Numéro de la salle"),
    croissant: bool = Query(None, description="Retourne les réservations dans l'ordre croissant"),
    prof: str = Query(None, description="Retourne les réservations du professeur concerné par nom"),
    reservation_id: int = Query(None, description="Retourne les informations d'une certaine réservation par son ID")
):
    if reservation_id is not None:
        reservation = get_reservation_by_id(reservation_id)
        if not reservation:
            raise HTTPException(status_code=410, detail="Aucune réservation trouvée pour cet ID")
        return [reservation]

    elif salle and croissant:
        reservations = get_reservations_by_salle_increase(salle)
    elif prof and croissant:
        reservations = get_reservations_by_prof_increase(prof)
    else:
        reservations = get_all_reservations()

    if not reservations:
        raise HTTPException(status_code=410, detail="Aucune réservation trouvée")

    return reservations


@router.delete(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Réservation supprimée avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Réservation supprimée avec succès"}
                }
            }
        },
        400: {
            "description": "Erreur lors de la suppression",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la suppression"}
                }
            }
        },
        422: {
            "description": "Paramètres insuffisants",
            "content": {
                "application/json": {
                    "example": {
                        "detail": "Paramètres insuffisants. Fournir soit id_reservation, soit date + numero_salle + heure_debut"
                    }
                }
            }
        }
    }
)
def delete_reservation(reservation: ReservationDelete, user_id: int = Depends(verify_token)):
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
        return {"message": "Réservation supprimée avec succès"}
    else:
        raise HTTPException(status_code=400, detail=result.get("message", "Erreur lors de la suppression"))


@router.put(
    "/",
    response_model=dict,
    status_code=status.HTTP_200_OK,
    responses={
        200: {
            "description": "Réservation mise à jour avec succès",
            "content": {
                "application/json": {
                    "example": {"message": "Réservation modifiée avec succès"}
                }
            }
        },
        400: {
            "description": "Erreur lors de la mise à jour",
            "content": {
                "application/json": {
                    "example": {"detail": "Erreur lors de la mise à jour"}
                }
            }
        }
    }
)
def update_reservation_endpoint(reservation: ReservationUpdate, user_id: int = Depends(verify_token)):
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
