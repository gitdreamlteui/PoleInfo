"""Routes pour la gestion des réservations"""
from db.fake_db import fake_reservation_db
from models.schemas import ReservationCreate, ReservationResponse
from core.auth import verify_token

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional
from datetime import date

from models.reservation import get_all_reservations

router = APIRouter(
    prefix="/reservations",
    tags=["reservations"]
)

### POST RESERVATION (ENCORE SUR FAKE DB)
@router.post("/", response_model=dict)
def create_reservation(reservation: ReservationCreate, username: str = Depends(verify_token)):
    """Créer une nouvelle réservation (protégée par authentification)"""
    reservation_id = len(fake_reservation_db) + 1
    fake_reservation_db[reservation_id] = {
        "id_salle": reservation.salle,
        "id_user": reservation.user,
        "id_matiere": reservation.matiere,
        "id_creneau": reservation.horaire_debut,
        "duree" : reservation.duree,
        "date": reservation.date,
        "info": reservation.info
    }
    return {"message": f"Réservation enregistrée par {username}.", "id": reservation_id}

### GET RESERVATION
@router.get("/", response_model=List[ReservationResponse])
def get_reservations():
    reservations = get_all_reservations()
    if not reservations:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune réservation trouvée"
        )
    
    return reservations
