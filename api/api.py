from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from passlib.context import CryptContext

app = FastAPI()

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

fake_users_db = {}
fake_reservation_db = {}

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

def hash_password(password: str) -> str:
    return pwd_context.hash(password)

@app.post("/users/", response_model=UserResponse)
def create_user(user: UserCreate):
    user_id = len(fake_users_db) + 1
    hashed_password = hash_password(user.password)
    
    fake_users_db[user_id] = {"username": user.username, "password": hashed_password}
    return {"id": user_id, "username": user.username}

@app.get("/users/", response_model=list[UserResponse])
def get_users():
    if not fake_users_db:
        raise HTTPException(status_code=404, detail="Aucun utilisateur")
    return [{"id": user_id, "username": data["username"]} for user_id, data in fake_users_db.items()]

@app.get("/users/{user_id}", response_model=UserResponse)
def get_user(user_id: int):
    user = fake_users_db.get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="Utilisateur non trouvé")
    
    return {"id": user_id, "username": user["username"]}

@app.post("/reservations/", response_model=dict)
def create_reservation(reservation: ReservationCreate):
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

    return {"message": "Réservation enregistrée.", "id": reservation_id}

@app.get("/reservations/", response_model=list[ReservationResponse])
def get_reservations():
    if not fake_reservation_db:
        raise HTTPException(status_code=404, detail="Aucune réservation")

    return [
        {"id": res_id, **data}
        for res_id, data in fake_reservation_db.items()
    ]
