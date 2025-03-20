# db/reservation_repository.py
from db.database import get_db_cursor
from models.schemas import ReservationCreate, ReservationResponse
from datetime import date

def create_reservation(reservation: ReservationCreate, id_user: int):
    """Créer une nouvelle réservation dans la base de données"""
    with get_db_cursor() as cursor:
        query = """
        INSERT INTO reservation (id_salle, id_matiere, id_creneau, id_user, duree, date, info)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        values = (
            reservation.id_salle,
            reservation.id_matiere,
            reservation.id_creneau,
            id_user,
            reservation.duree,
            reservation.date,
            reservation.info
        )
        cursor.execute(query, values)
        return cursor.lastrowid

def get_all_reservations():
    """Récupérer toutes les réservations"""
    with get_db_cursor() as cursor:
        query = """
        SELECT id_reservation, id_salle, id_matiere, id_creneau, id_user, duree, date, info 
        FROM reservation
        """
        cursor.execute(query)
        return cursor.fetchall()

def get_reservation_by_id(reservation_id: int):
    """Récupérer une réservation par son ID"""
    with get_db_cursor() as cursor:
        query = """
        SELECT id_reservation, id_salle, id_matiere, id_creneau, id_user, duree, date, info 
        FROM reservation 
        WHERE id_reservation = %s
        """
        cursor.execute(query, (reservation_id,))
        return cursor.fetchone()
