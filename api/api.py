"""
API d'exemple pour une gestion d'utilisateurs
Dans le contexte, pour ajouter des professeurs.
"""

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from passlib.context import CryptContext

app = FastAPI()

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

fake_users_db = {}

class UserCreate(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str

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
    return [{"id": user_id, "username": data["username"]} for user_id, data in fake_users_db.items()]

@app.get("/users/{user_id}", response_model=UserResponse)
def get_user(user_id: int):
    user = fake_users_db.get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="Utilisateur non trouvé")
    
    return {"id": user_id, "username": user["username"]}

