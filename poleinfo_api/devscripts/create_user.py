import requests

URL = "http://127.0.0.1:8000/users/"

DATA = {
  "username": "dev_account",
  "password": "ciel"
}

r = requests.post(url=URL, json=DATA)

print(r.status_code)
print("Utilisateur ajouté!")
