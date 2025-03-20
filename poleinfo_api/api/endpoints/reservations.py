# routes/reservation.py
"""Routes pour la gestion des réservations"""
from fastapi import APIRouter, HTTPException, Depends, status
from models.schemas import ReservationCreate, ReservationResponse
from core.auth import verify_token, get_user_id
from db.reservation_repository import create_reservation, get_all_reservations, get_reservation_by_id
from mysql.connector import Error

router = APIRouter()

@router.post("/", response_model=dict)
def create_reservation_endpoint(reservation: ReservationCreate, user_info: dict = Depends(verify_token)):
    """Créer une nouvelle réservation (protégée par authentification)"""
    try:
        # Supposons que verify_token renvoie un dictionnaire contenant l'ID et le nom d'utilisateur
        id_user = user_info["id_user"]
        username = user_info["username"]
        
        reservation_id = create_reservation(reservation, id_user)
        return {"message": f"Réservation enregistrée par {username}.", "id_reservation": reservation_id}
    except Error as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Erreur lors de la création de la réservation: {str(e)}"
        )

@router.get("/", response_model=list[ReservationResponse])
def get_reservations_endpoint():
    """Récupérer la liste des réservations"""
    try:
        reservations = get_all_reservations()
        if not reservations:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Aucune réservation"
            )
        return reservations
    except Error as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Erreur lors de la récupération des réservations: {str(e)}"
        )

@router.get("/{reservation_id}", response_model=ReservationResponse)
def get_reservation_endpoint(reservation_id: int):
    """Récupérer une réservation par son ID"""
    try:
        reservation = get_reservation_by_id(reservation_id)
        if not reservation:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Réservation non trouvée"
            )
        return reservation
    except Error as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Erreur lors de la récupération de la réservation: {str(e)}"
        )
