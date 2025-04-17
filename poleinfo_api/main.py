"""
PoleInfo - API
--------------
Auteur : Elias GAUTHIER
Date de création : 07/02/2025
Description : Application principale de l'API de gestion des réservations de salles du Pôle Info
"""

from fastapi import FastAPI
from api.endpoints import auth, users, reservations, creneaux, salles, matieres, classes
from datetime import datetime
import locale


# Création de l'application FastAPI avec métadonnées pour Swagger UI
app = FastAPI(
    title="PoleInfo API", 
    description="""
    API de gestion des réservations de salles
    
    Cette API permet la gestion complète des réservations de salles pour le Pôle Info.
    
    Fonctionnalités principales
    
    - Authentification des utilisateurs
    - Gestion des utilisateurs (création, consultation, suppression)
    - Gestion des réservations de salles
    - Gestion des salles, créneaux, matières et classes
    
    Développé par Elias GAUTHIER avec la co-conception d'Ethan CLEMENT
    """,
    version="1.0.0",
    contact={
        "name": "Pôle Info",
        "email": "elias.gauthier@lp2i-poitiers.fr"
    }
)

# Inclusion des routeurs pour organiser les endpoints par domaine
app.include_router(auth.router)
app.include_router(users.router, prefix="/utilisateurs", tags=["utilisateurs"])
app.include_router(reservations.router, prefix="/reservations", tags=["reservations"])
app.include_router(creneaux.router, prefix="/creneaux", tags=["creneaux"])
app.include_router(salles.router, prefix="/salles", tags=["salles"])
app.include_router(matieres.router, prefix="/matieres", tags=["matieres"])
app.include_router(classes.router, prefix="/classes", tags=["classes"])


@app.get("/", tags=["accueil"])
def read_root():
    """
    Point d'entrée principal de l'API qui affiche un message de bienvenue
    avec la date du jour au format français.
    """
    locale.setlocale(locale.LC_TIME, 'fr_FR.UTF-8')
    now = datetime.now()
    formatted_date = now.strftime("%d %B %Y")
    
    return {
        "message": f"Bienvenue sur l'API PoleInfo, nous sommes le {formatted_date}",
        "documentation": "/docs",
        "version": "1.0.0",
        "auteur": "Elias GAUTHIER"
    }


if __name__ == "__main__":
    # Message affiché au démarrage du serveur en mode autonome
    print("=" * 50)
    print("  PoleInfo API - Système de gestion des réservations")
    print("  Développé par Elias GAUTHIER")
    print("=" * 50)
    print("  Documentation disponible à l'adresse: http://192.168.8.152:8000/docs")
    print("=" * 50)
    
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
