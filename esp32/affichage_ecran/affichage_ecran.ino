#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <PubSubClient.h>

// Wi-Fi
const char* ssid = "WIFI_LABO";
const char* password = "EpsiWis2018!";

// MQTT
const char* mqtt_server = "172.16.118.56";
const int mqtt_port = 1883;
const char* topic = "labo/esp32";

WiFiClient espClient;
PubSubClient client(espClient);

// ====== OLED ======
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET    -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// ====== Fonction callback MQTT ======
void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  Serial.println("Message reçu : " + message);

  // Affichage sur OLED
  display.clearDisplay();
  display.setCursor(0, 10);
  display.setTextSize(2);
  display.setTextColor(WHITE);
  display.println(message);
  display.display();
}

// ====== Reconnexion MQTT ======
void reconnect() {
  while (!client.connected()) {
    Serial.print("Connexion au broker MQTT...");
    if (client.connect("ESP32Client")) {
      Serial.println("Connecté !");
      client.subscribe(topic);
    } else {
      Serial.print("Échec, rc=");
      Serial.print(client.state());
      Serial.println(" Reconnexion dans 2s...");
      delay(2000);
    }
  }
}

void setup() {
  Serial.begin(115200);

  // ===== OLED =====
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Erreur OLED");
    for(;;);
  }
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);
  display.setCursor(0,10);
  display.println("Connexion Wi-Fi...");
  display.display();

  // ===== Wi-Fi =====
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Wi-Fi connecté !");
  display.clearDisplay();
  display.setCursor(0,10);
  display.setTextSize(1);
  display.println("Wi-Fi connecté !");
  display.display();

  // ===== MQTT =====
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) {
      reconnect();
    }
    client.loop();
}