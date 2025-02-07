"""
PoleInfo - API
Auteur : Elias GAUTHIER
Date : 07/02/2025
"""

from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from pydantic import BaseModel
from passlib.context import CryptContext
import jwt as pyjwt
import datetime

app = FastAPI()

# Sécurité et base de données fictive
SECRET_KEY = "BTSCIEL"
ALGORITHM = "HS256"
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

fake_users_db = {}  # Stockage temporaire des utilisateurs
fake_reservation_db = {}  # Stockage des réservations

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

# Modèles de données
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

# Fonction pour hacher les mots de passe
def hash_password(password: str) -> str:
    return pwd_context.hash(password)

# Fonction pour créer un token JWT
def create_access_token(username: str):
    payload = {
        "sub": username,
        "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1)
    }
    return pyjwt.encode(payload, SECRET_KEY, algorithm=ALGORITHM)

# Vérification du token JWT
def verify_token(token: str = Depends(oauth2_scheme)):
    try:
        payload = pyjwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        username = payload.get("sub")

        if username not in [user["username"] for user in fake_users_db.values()]:
            raise HTTPException(status_code=401, detail="Utilisateur non autorisé")
        
        return username
    
    except pyjwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expiré")
    except pyjwt.InvalidTokenError:
        raise HTTPException(status_code=401, detail="Token invalide")

# Route de connexion pour générer un token JWT
@app.post("/token")
def login(form_data: OAuth2PasswordRequestForm = Depends()):
    """ Authentification des utilisateurs existants """
    user = next((u for u in fake_users_db.values() if u["username"] == form_data.username), None)
    
    if not user or not pwd_context.verify(form_data.password, user["password"]):
        raise HTTPException(status_code=400, detail="Identifiants incorrects")
    
    token = create_access_token(user["username"])
    return {"access_token": token, "token_type": "bearer"}

# Ajout d'utilisateur (Non protégé)
@app.post("/users/", response_model=UserResponse)
def create_user(user: UserCreate):
    """ Permet d'ajouter un utilisateur (protégé si nécessaire) """
    user_id = len(fake_users_db) + 1
    hashed_password = hash_password(user.password)
    
    fake_users_db[user_id] = {"username": user.username, "password": hashed_password}
    return {"id": user_id, "username": user.username}

# Récupérer la liste des utilisateurs (Non protégé)
@app.get("/users/", response_model=list[UserResponse])
def get_users():
    if not fake_users_db:
        raise HTTPException(status_code=404, detail="Aucun utilisateur")
    return [{"id": user_id, "username": data["username"]} for user_id, data in fake_users_db.items()]

# Récupérer un utilisateur spécifique (Non protégé)
@app.get("/users/{user_id}", response_model=UserResponse)
def get_user(user_id: int):
    user = fake_users_db.get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="Utilisateur non trouvé")
    
    return {"id": user_id, "username": user["username"]}

# Ajouter une réservation (Protégé)
@app.post("/reservations/", response_model=dict)
def create_reservation(reservation: ReservationCreate, user: str = Depends(verify_token)):
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
    return {"message": f"Réservation enregistrée par {user}.", "id": reservation_id}

# Voir toutes les réservations
@app.get("/reservations/", response_model=list[ReservationResponse])
def get_reservations():
    if not fake_reservation_db:
        raise HTTPException(status_code=404, detail="Aucune réservation")
    return [{"id": res_id, **data} for res_id, data in fake_reservation_db.items()]

# Voir une réservation spécifique
@app.get("/reservations/{reservation_id}", response_model=ReservationResponse)
def get_reservation(reservation_id: int):
    reservation = fake_reservation_db.get(reservation_id)
    if not reservation:
        raise HTTPException(status_code=404, detail="Réservation non trouvée")
    return {"id": reservation_id, **reservation}
