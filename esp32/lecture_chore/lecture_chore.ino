#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <HardwareSerial.h>
#include <DFRobotDFPlayerMini.h>

// ---------- CONFIG WIFI ----------
const char* ssid = "WIFI_LABO";
const char* password = "EpsiWis2018!";

// ---------- MQTT ----------
const char* mqtt_server = "172.16.118.56";
const char* mqtt_topic  = "bisik";

// ---------- Servo ----------
Servo servo;
int servoPin = 12;

// ---------- OLED ----------
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_SDA 33
#define OLED_SCL 25
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// ---------- DFPLAYER ----------
HardwareSerial dfSerial(2); 
DFRobotDFPlayerMini dfPlayer;

// ---------- MQTT CLIENT ----------
WiFiClient espClient;
PubSubClient client(espClient);

// ================================================================
//                      CALLBACK MQTT
// ================================================================
void callback(char* topic, byte* payload, unsigned int length) {
    // Convertir payload en String
    String jsonString = "";
    for (unsigned int i = 0; i < length; i++) {
        jsonString += (char)payload[i];
    }

    Serial.println("JSON reçu :");
    Serial.println(jsonString);

    DynamicJsonDocument doc(4096);
    if (deserializeJson(doc, jsonString)) {
        Serial.println("Erreur JSON !");
        return;
    }

    // ----------- Affichage messages -----------
    if (doc.containsKey("messages")) {
        JsonArray arr = doc["messages"];
        for (JsonObject msg : arr) {
            const char* txt = msg["message"];
            int duree = msg["duree"];

            Serial.print("Message : "); Serial.println(txt);

            display.clearDisplay();
            display.setCursor(0,0);
            display.setTextSize(2);
            display.setTextColor(SSD1306_WHITE);
            display.println(txt);
            display.display();

            delay(duree);
        }
    }

    // ----------- Mouvements servo -----------
    if (doc.containsKey("mouvements")) {
        JsonArray arr = doc["mouvements"];
        for (JsonObject mv : arr) {
            int angle = mv["angle"];
            int duree = mv["duree"];

            Serial.printf("Servo → %d° (%d ms)\n", angle, duree);
            servo.write(angle);
            delay(duree);
        }
    }

    // ----------- Son DFPlayer -----------
    if (doc.containsKey("son")) {
        const char* file = doc["son"];
        int num = 0;

        sscanf(file, "Robot%d.mp3", &num);
        if (num > 0) {
            Serial.printf("Lecture du fichier %d\n", num);
            dfPlayer.playFolder(1, num); // Dossier /01/
        } else {
            Serial.println("Nom de fichier invalide !");
        }
    }

    // ----------- Volume DFPlayer -----------
    if (doc.containsKey("volume")) {
        int vol = doc["volume"];
        dfPlayer.volume(vol);
        Serial.printf("Volume → %d\n", vol);
    }
}

// ================================================================
//                RECONNEXION MQTT
// ================================================================
void reconnect() {
    while (!client.connected()) {
        Serial.println("Connexion MQTT...");
        if (client.connect("ESP32_Client")) {
            Serial.println("MQTT OK");
            client.subscribe(mqtt_topic);
        } else {
            Serial.printf("Échec, rc=%d → retry dans 2s\n", client.state());
            delay(2000);
        }
    }
}

// ================================================================
//                       SETUP
// ================================================================
void setup() {
    Serial.begin(115200);

    // ---- Servo ----
    ESP32PWM::allocateTimer(0);
    servo.setPeriodHertz(50);
    servo.attach(servoPin, 500, 2400);

    // ---- OLED ----
    Wire.begin(OLED_SDA, OLED_SCL);
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println("Erreur OLED !");
        while(1);
    }
    display.clearDisplay();
    display.setTextSize(2);
    display.setTextColor(SSD1306_WHITE);
    display.setCursor(0,0);
    display.println("READY");
    display.display();

    // ---- DFPlayer ----
    dfSerial.begin(9600, SERIAL_8N1, 16, 17);
    if (!dfPlayer.begin(dfSerial)) {
        Serial.println("Erreur DFPlayer !");
    } else {
        dfPlayer.volume(20);
    }

    // ---- WiFi ----
    WiFi.begin(ssid, password);
    Serial.print("Connexion WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connecté !");

    // ---- MQTT ----
    client.setServer(mqtt_server, 1883);
    client.setCallback(callback);
}

// ================================================================
//                         LOOP
// ================================================================
void loop() {
    if (!client.connected()) reconnect();
    client.loop();
}
