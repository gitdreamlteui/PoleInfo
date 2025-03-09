<div align="center">
	<h1>Pole Info</h1>
</div>

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




