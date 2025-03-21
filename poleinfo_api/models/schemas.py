"""Modèles de données Pydantic"""
from pydantic import BaseModel
from typing import Optional
from datetime import date
from datetime import timedelta
class UserCreate(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str

class ReservationCreate(BaseModel):
    duree: float
    date: date
    numero_salle: str
    nom_matiere: str
    heure_debut: timedelta
    nom_user: str
    prenom: str
    noms_classes: str
    info: Optional[str] = None

class ReservationResponse(BaseModel):
    id_reservation: int
    duree: float
    date: date
    info: str
    numero_salle: str
    capacite_salle: int
    type_salle: str
    nom_matiere: str
    heure_debut: timedelta
    nom_user: str
    prenom: str
    noms_classes: str


class Token(BaseModel):
    access_token: str
    token_type: str