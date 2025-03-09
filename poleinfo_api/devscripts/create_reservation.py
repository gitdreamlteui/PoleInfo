import requests

BASE_URL = "http://127.0.0.1:8000"
TOKEN_URL = f"{BASE_URL}/token"
RESERVATION_URL = f"{BASE_URL}/reservations/"

USER_DATA = {
    "username": "test",
    "password": "mdpethan"
}

# Obtenir un token JWT
auth_response = requests.post(TOKEN_URL, data=USER_DATA)
if auth_response.status_code == 200:
    token = auth_response.json().get("access_token")
else:
    print("❌ Erreur d'authentification")
    exit()

# Faire une réservation avec le token
HEADERS = {"Authorization": f"Bearer {token}"}

DATA = {
  "salle": "3W03",
  "matiere": "Informatique",
  "prof": "Domsutter x Alanus",
  "classe": "BTS CIEL2",
  "horaire_debut": "8h10",
  "horaire_fin": "9h00",
  "date": "07-02-2024",
  "info": "Cours d'informatique"
}

r = requests.post(url=RESERVATION_URL, json=DATA, headers=HEADERS)

print(r.status_code)
print(r.json())
