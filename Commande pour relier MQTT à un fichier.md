
Installer le serveur MQTT sur la VM : 

sudo apt install mosquitto mosquitto-clients
sudo systemctl enable mosquitto
sudo systemctl start mosquitto

Dans le powershell pour installer les clients mosquitto sur le pc : 

choco install mosquitto

Pour envoyer un message de du PC vers la VM : 

mosquitto_pub -h 172.16.110.2 -p 1883 -t "test/topic" -m "Hello depuis Windows"


Commande pour se connecter au topic sur le serveur Mosquitto sur la VM : 

mosquitto_sub -h localhost -t test/topic

Commande pour envoyer un texte sur le serveur de la VM : 

mosquitto_sub -h localhost -t test/topic

