"""
PoleInfo - API
Auteur : Elias GAUTHIER
Date : 07/02/2025
"""

from fastapi import FastAPI
from api.endpoints import auth, users, reservations
from datetime import datetime

now = datetime.now()
formatted_date = now.strftime("%d %B")
app = FastAPI(title="PoleInfo API", description="API de gestion des réservations de salles")

# Inclusion des routeurs
app.include_router(auth.router)
app.include_router(users.router, prefix="/users", tags=["users"])
app.include_router(reservations.router, prefix="/reservations", tags=["reservations"])

@app.get("/")
def read_root():
    return {"message": f"Bienvenue sur l'API PoleInfo, nous somme le {formatted_date}"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)