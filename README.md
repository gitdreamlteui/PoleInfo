<div align="center">
	<h1>Pole Info</h1>
</div>

# Déploiment de l'API
## Lancer l'API en local
```py
python3 -m venv env
source env/bin/activate
pip -r requirements.txt
python main.py
```

## Ajouter l'api au systemd du raspbery pi
Prérequis : environnement virtuel et dépendances installées
```
sudo nano /etc/systemd/system/poleinfo_api.service
```
Mettre ça en avec le bon utilisateur : 
```
[Unit]
Description=API FastAPI
After=network.target

[Service]
User=elias
WorkingDirectory=/home/elias/PoleInfo/poleinfo_api
ExecStart=/home/elias/PoleInfo/poleinfo_api/venv/bin/python main.py
Restart=always

[Install]
WantedBy=multi-user.target
```
<b>Activer le service :</b>
``` 
sudo systemctl daemon-reload
sudo systemctl enable poleinfo_api
sudo systemctl start poleinfo_api
````
Vérification : 
```
sudo systemctl status poleinfo_api
```
## SSH
### Sur pc de dev :
```
sudo apt install openssh-server
sudo systemctl enable --now ssh
```
Créer la clé : 
```
ssh-keygen -t rsa -b 4096 -f ~/.ssh/ansible_key -N ""
```
Donner la clé publique au raspbery
```
ssh-copy-id -i ~/.ssh/ansible_key.pub <USER>@192.168.X.X
```
Tester la connexion : 
```
ssh -i ~/.ssh/ansible_key <USER>@192.168.X.X
```

## Configurer Ansible sur pc de dev

```
sudo apt install -y ansibl
ansible --version
```
<b> Fichiers : </b>
- inventory.ini 
- deploy.yml
> (Deja dans le repo, juste a modifier les chemins etc)

Déploiement avec ansible : 
`ansible-playbook -i inventory.ini deploy.yml
`
## Github action (pas fait)
*.github/workflows/deploy.yml* -> pas le fichier deploy.yml précédent
```
name: Deploy with Ansible

on:
  push:
    branches:
      - dev

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Récupérer le code
        uses: actions/checkout@v3

      - name: Installer Ansible
        run: sudo apt update && sudo apt install -y ansible

      - name: Déployer via Ansible
        run: ansible-playbook -i inventory.ini deploy.yml
```



