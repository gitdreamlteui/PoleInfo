"""Modèles de données Pydantic"""
from pydantic import BaseModel
from typing import Optional
from datetime import date
from datetime import timedelta

class UserBase(BaseModel):
    login: str
    type: int
    nom: str
    prenom: str

class SalleBase(BaseModel):
    capacite: int
    type: str

#--------------------------------------------------#
    
class UserCreate(UserBase):
    password: str

class UserResponse(UserBase):
    id_user: int

class UserDelete(BaseModel):
    login: str

#--------------------------------------------------#

class MatiereCreate(BaseModel):
    nom: str

class MatiereResponse(BaseModel):
    nom: str
    
class MatiereDelete(BaseModel):
    nom: str
#--------------------------------------------------#

class SalleCreate(SalleBase):
    numero: str
    
class SalleResponse(SalleBase):
    numero: str
    id_salle: int
    
class SalleDelete(BaseModel):
    numero: str

#--------------------------------------------------#

class ReservationCreate(BaseModel):
    duree: float
    date: date
    numero_salle: str
    nom_matiere: str
    heure_debut_creneau: str
    login_user: str
    nom_classe: str
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
    
class ReservationDelete(BaseModel):
    id_reservation: Optional[int] = None
    date: Optional[date]
    numero_salle: Optional[str] = None
    heure_debut: Optional[str] = None
#--------------------------------------------------#

class Token(BaseModel):
    access_token: str
    token_type: str
    user_type: int
    user_name: str

#--------------------------------------------------#

class CreneauCreate(BaseModel):
    heure_debut: timedelta

class CreneauResponse(BaseModel):
    heure_debut: timedelta
    
class CreneauDelete(BaseModel):
    heure_debut: timedelta
    
#--------------------------------------------------#

class ClasseCreate(BaseModel):
    nom: str

class ClasseResponse(BaseModel):
    nom: str
    
class ClasseDelete(BaseModel):
    nom: str

#--------------------------------------------------#