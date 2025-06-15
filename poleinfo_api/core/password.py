#core/password.py


"""
API Pôle Info
--------------

Auteur : Elias GAUTHIER
Dernière date de mise à jour : 28/03/2025

Description : ce programme fournit les fonctions essentielles pour la sécurité 
des mots de passe, notamment le hachage et la vérification des mots de passe
utilisateurs. Ces fonctions sont utilisées dans les processus d'authentification
et d'enregistrement des utilisateurs.
"""

from passlib.context import CryptContext

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

def hash_password(password: str) -> str:
    return pwd_context.hash(password)

def verify_password(plain_password: str, hashed_password: str) -> bool:
    return pwd_context.verify(plain_password, hashed_password)
