"""
PoleInfo - API
Auteur : Elias GAUTHIER
Date : 07/02/2025
"""

from fastapi import FastAPI
from api.endpoints import auth, users, reservations 
from datetime import datetime
import locale


app = FastAPI(title="PoleInfo API", description="API de gestion des réservations de salles")

# Inclusion des routeurs
app.include_router(auth.router)
app.include_router(users.router, prefix="/utilisateurs", tags=["utilisateurs"])
app.include_router(reservations.router, prefix="/reservations", tags=["reservations"])

@app.get("/")
def read_root():
    locale.setlocale(locale.LC_TIME, 'fr_FR.UTF-8')
    now = datetime.now()
    formatted_date = now.strftime("%d %B")
    return {"message": f"Bienvenue sur l'API PoleInfo, nous sommes le {formatted_date}"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)