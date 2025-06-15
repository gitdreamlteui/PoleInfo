# db/database.py

import mysql.connector
from mysql.connector import Error
from contextlib import contextmanager

DB_CONFIG = {
    'host': '192.168.8.152',
    'database': 'poleinfo',
    'user': 'root',
    'password': 'cielPOLEINFO25**'
}

@contextmanager
def get_db_connection():
    """Gestionnaire de contexte pour la connexion à la base de données"""
    connection = None
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        yield connection
    except Error as e:
        print(f"Erreur de connexion à la base de données: {e}")
        raise
    finally:
        if connection and connection.is_connected():
            connection.close()

@contextmanager
def get_db_cursor():
    """Gestionnaire de contexte pour obtenir un curseur de base de données"""
    with get_db_connection() as connection:
        cursor = connection.cursor(dictionary=True)
        try:
            yield cursor
            connection.commit()
        except Error as e:
            connection.rollback()
            print(f"Erreur d'exécution SQL: {e}")
            raise
        finally:
            cursor.close()
