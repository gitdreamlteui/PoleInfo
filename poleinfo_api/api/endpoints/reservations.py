"""Routes pour la gestion des réservations"""
from db.fake_db import fake_reservation_db
from models.schemas import ReservationCreate, ReservationResponse
from core.auth import verify_token

from fastapi import APIRouter, Depends, HTTPException, status, Query
from typing import List, Optional
from datetime import date

from models.reservation import get_all_reservations, get_reservations_by_salle, post_reservation

router = APIRouter(
    tags=["reservations"]
)
### POST RESERVATION
@router.post("/", response_model=dict)
def create_reservation(reservation: ReservationCreate, username: str = Depends(verify_token)):
    """Créer une nouvelle réservation (protégée par authentification)"""
    reservation = {
        "duree": reservation.duree,
        "date": reservation.date,
        "numero_salle": reservation.numero_salle,
        "nom_matiere" : reservation.nom_matiere,
        "prenom" : reservation.prenom,
        "heure_debut": reservation.heure_debut,
        "nom_classe" : reservation.noms_classes,
        "info": reservation.info
    }
    post_reservation(reservation);
    return {"message": f"Réservation enregistrée par {username}.", "id": reservation_id}

### GET RESERVATION

@router.get("/", response_model=List[ReservationResponse])
def get_reservations(salle: str = Query(None, description="Numéro de la salle")):
    if salle is not None:
        reservations = get_reservations_by_salle(salle)
    else:
        reservations = get_all_reservations()
    
    if not reservations:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune réservation trouvée"
        )
    
    return reservations
