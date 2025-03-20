"""Modèles de données Pydantic"""
from pydantic import BaseModel
from typing import Optional
from datetime import date

class UserCreate(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str

class ReservationCreate(BaseModel):
    id_salle: int
    id_matiere: int
    id_creneau: int
    duree: int
    date: date
    info: Optional[str] = None

class ReservationResponse(BaseModel):
    id_reservation: int
    id_salle: int
    id_matiere: int
    id_creneau: int
    id_user: int
    duree: int
    date: date
    info: Optional[str] = None
    
class Token(BaseModel):
    access_token: str
    token_type: str