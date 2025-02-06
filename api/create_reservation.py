import requests

URL = "http://127.0.0.1:8000/reservations/"

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

r = requests.post(url=URL, json=DATA)

print(r.status_code)
print("Réservation ajoutée!")
