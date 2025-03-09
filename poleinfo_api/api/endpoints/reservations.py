"""Routes pour la gestion des réservations"""
from fastapi import APIRouter, HTTPException, Depends, status
from db.fake_db import fake_reservation_db
from models.schemas import ReservationCreate, ReservationResponse
from core.auth import verify_token

router = APIRouter()

@router.post("/", response_model=dict)
def create_reservation(reservation: ReservationCreate, username: str = Depends(verify_token)):
    """Créer une nouvelle réservation (protégée par authentification)"""
    reservation_id = len(fake_reservation_db) + 1
    fake_reservation_db[reservation_id] = {
        "salle": reservation.salle, 
        "matiere": reservation.matiere,
        "prof": reservation.prof,
        "classe": reservation.classe,
        "horaire_debut": reservation.horaire_debut,
        "horaire_fin": reservation.horaire_fin,
        "date": reservation.date,
        "info": reservation.info
    }
    return {"message": f"Réservation enregistrée par {username}.", "id": reservation_id}

@router.get("/", response_model=list[ReservationResponse])
def get_reservations():
    """Récupérer la liste des réservations"""
    if not fake_reservation_db:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Aucune réservation"
        )
    return [{"id": res_id, **data} for res_id, data in fake_reservation_db.items()]

@router.get("/{reservation_id}", response_model=ReservationResponse)
def get_reservation(reservation_id: int):
    """Récupérer une réservation par son ID"""
    reservation = fake_reservation_db.get(reservation_id)
    if not reservation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Réservation non trouvée"
        )
    return {"id": reservation_id, **reservation}