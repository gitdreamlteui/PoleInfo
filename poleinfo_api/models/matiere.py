"""Fonctions d'accès aux données des matières"""
from db.database import get_db_cursor
from typing import List, Dict, Any

def get_all_matieres() -> List[Dict[int, Any]]:
    with get_db_cursor() as cursor:
        query = "SELECT nom FROM matiere"
        cursor.execute(query)
        results = cursor.fetchall()
    return results

def delete_matieres(nom: str) -> None:
    with get_db_cursor() as cursor:
        query = "DELETE FROM matiere WHERE nom = %s"
        cursor.execute(query, (nom,))

def create_matiere(nom: str) -> None:
    with get_db_cursor() as cursor:
        query = "INSERT INTO matiere (nom) VALUES (%s)"
        cursor.execute(query, (nom,))


