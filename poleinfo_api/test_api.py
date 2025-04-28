import pytest
from fastapi.testclient import TestClient
from unittest.mock import patch, MagicMock
from datetime import date, time
import json
import sys
sys.path.append('.')
import pytest
from main import app

# Création du client de test
client = TestClient(app)
# Fixtures pour les tests
@pytest.fixture
def mock_db_session():
    """Crée un mock de session de base de données"""
    mock_session = MagicMock()
    return mock_session

@pytest.fixture
def auth_headers():
    """Simule les headers d'authentification"""
    return {"Authorization": "Bearer fake_token_for_testing"}

@pytest.fixture
def admin_user():
    """Données d'un utilisateur administrateur"""
    return {
        "id_user": 1,
        "login": "admin",
        "type": 1,  # Type admin
        "nom": "Admin",
        "prenom": "Test"
    }

@pytest.fixture
def normal_user():
    """Données d'un utilisateur standard"""
    return {
        "id_user": 2,
        "login": "prof",
        "type": 2,  # Type utilisateur
        "nom": "Professeur",
        "prenom": "Test"
    }

@pytest.fixture
def sample_reservation():
    """Exemple de données de réservation"""
    return {
        "duree": 2,
        "date": "2025-04-25",
        "numero_salle": "I103",
        "nom_matiere": "Informatique",
        "heure_debut_creneau": "08:00:00",
        "login_user": "prof",
        "nom_classe": "NSI",
        "info": "Cours programmation"
    }

@pytest.fixture
def sample_salle():
    """Exemple de données de salle"""
    return {
        "capacite": 30,
        "type": "Informatique",
        "numero": "I103"
    }

# Tests pour l'authentification
class TestAuthentication:
    
    @patch("app.routes.auth.authenticate_user")
    @patch("app.routes.auth.create_access_token")
    def test_login_success(self, mock_create_token, mock_auth_user, client):
        """Test d'une authentification réussie"""
        # Configuration des mocks
        mock_auth_user.return_value = {"id_user": 1, "type": 1, "nom": "Admin"}
        mock_create_token.return_value = "fake_token"
        
        # Envoi de la requête
        response = client.post(
            "/token",
            data={"username": "admin", "password": "password"},
            headers={"Content-Type": "application/x-www-form-urlencoded"}
        )
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert data["access_token"] == "fake_token"
        assert data["token_type"] == "bearer"
        assert data["user_type"] == 1
        assert "user_name" in data
    
    @patch("app.routes.auth.authenticate_user")
    def test_login_failed(self, mock_auth_user, client):
        """Test d'une authentification échouée"""
        # Le mock retourne None (échec d'authentification)
        mock_auth_user.return_value = None
        
        # Envoi de la requête
        response = client.post(
            "/token",
            data={"username": "unknown", "password": "wrong"},
            headers={"Content-Type": "application/x-www-form-urlencoded"}
        )
        
        # Vérifications
        assert response.status_code == 400
        assert "detail" in response.json()

# Tests pour les réservations
class TestReservations:
    
    @patch("app.routes.reservations.get_user_by_id")
    @patch("app.dependencies.verify_token")
    def test_create_reservation_success(self, mock_verify_token, mock_get_user, client, sample_reservation, normal_user, auth_headers):
        """Test de création d'une réservation avec succès"""
        # Configuration des mocks
        mock_verify_token.return_value = normal_user["id_user"]
        mock_get_user.return_value = normal_user
        
        # Envoi de la requête
        response = client.post(
            "/reservations/",
            json=sample_reservation,
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert "message" in data
        assert "id" in data
    
    @patch("app.routes.reservations.get_user_by_id")
    @patch("app.dependencies.verify_token")
    def test_create_reservation_user_not_found(self, mock_verify_token, mock_get_user, client, sample_reservation, auth_headers):
        """Test de création d'une réservation avec un utilisateur inexistant"""
        # Configuration des mocks
        mock_verify_token.return_value = 999  # ID inexistant
        mock_get_user.return_value = None
        
        # Envoi de la requête
        response = client.post(
            "/reservations/",
            json=sample_reservation,
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 404
    
    def test_get_reservations_success(self, client):
        """Test de récupération des réservations"""
        # Envoi de la requête
        response = client.get("/reservations/")
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    def test_get_reservations_filtered(self, client):
        """Test de récupération des réservations avec filtres"""
        # Envoi de la requête avec filtres
        response = client.get("/reservations/?salle=I103&prof=Professeur&croissant=true")
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
        # Vérifier les filtres (si des données sont retournées)
        if data:
            assert data[0]["numero_salle"] == "I103"
            assert "Professeur" in data[0]["nom_user"]
    
    @patch("app.dependencies.verify_token")
    def test_delete_reservation_success(self, mock_verify_token, client, auth_headers):
        """Test de suppression d'une réservation"""
        # Configuration des mocks
        mock_verify_token.return_value = 1  # Admin user
        
        # Envoi de la requête
        response = client.delete(
            "/reservations/",
            json={"id_reservation": 1},
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 200
        assert "message" in response.json()

# Tests pour les salles
class TestSalles:
    
    def test_get_salles(self, client):
        """Test de récupération des salles"""
        response = client.get("/salles/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    @patch("app.dependencies.verify_token")
    @patch("app.routes.salles.is_admin")
    def test_add_salle_success(self, mock_is_admin, mock_verify_token, client, sample_salle, auth_headers):
        """Test d'ajout d'une salle avec succès"""
        # Configuration des mocks
        mock_verify_token.return_value = 1
        mock_is_admin.return_value = True
        
        # Envoi de la requête
        response = client.post(
            "/salles/",
            json=sample_salle,
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert "message" in data
        assert "id" in data
    
    @patch("app.dependencies.verify_token")
    @patch("app.routes.salles.is_admin")
    def test_add_salle_not_admin(self, mock_is_admin, mock_verify_token, client, sample_salle, auth_headers):
        """Test d'ajout d'une salle par un non-admin"""
        # Configuration des mocks
        mock_verify_token.return_value = 2
        mock_is_admin.return_value = False
        
        # Envoi de la requête
        response = client.post(
            "/salles/",
            json=sample_salle,
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 403  # Forbidden

# Tests pour les utilisateurs
class TestUtilisateurs:
    
    def test_get_users(self, client):
        """Test de récupération des utilisateurs"""
        response = client.get("/utilisateurs/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    @patch("app.dependencies.verify_token")
    @patch("app.routes.utilisateurs.is_admin")
    def test_add_user_success(self, mock_is_admin, mock_verify_token, client, auth_headers):
        """Test d'ajout d'un utilisateur avec succès"""
        # Configuration des mocks
        mock_verify_token.return_value = 1
        mock_is_admin.return_value = True
        
        # Données de test
        new_user = {
            "login": "new_prof",
            "type": 2,
            "nom": "Nouveau",
            "prenom": "Professeur",
            "password": "password123"
        }
        
        # Envoi de la requête
        response = client.post(
            "/utilisateurs/",
            json=new_user,
            headers=auth_headers
        )
        
        # Vérifications
        assert response.status_code == 200
        data = response.json()
        assert "message" in data
        assert "id" in data

# Tests pour les créneaux, matières et classes
class TestAutresEndpoints:
    
    def test_get_creneaux(self, client):
        """Test de récupération des créneaux"""
        response = client.get("/creneaux/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    def test_get_matieres(self, client):
        """Test de récupération des matières"""
        response = client.get("/matieres/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    def test_get_classes(self, client):
        """Test de récupération des classes"""
        response = client.get("/classes/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
    
    def test_root_endpoint(self, client):
        """Test du point d'entrée principal"""
        response = client.get("/")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, dict)
        assert "message" in data