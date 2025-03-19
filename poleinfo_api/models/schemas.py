"""Modèles de données Pydantic"""
from pydantic import BaseModel

class UserCreate(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str

class ReservationCreate(BaseModel):
    salle: str
    matiere: str
    prof: str
    classe: str
    horaire_debut: str
    horaire_fin: str
    date: str
    info: str

class ReservationResponse(BaseModel):
    id: int
    salle: str
    matiere: str
    prof: str
    classe: str
    horaire_debut: str
    horaire_fin: str
    date: str
    info: str

class Token(BaseModel):
    access_token: str
    token_type: str