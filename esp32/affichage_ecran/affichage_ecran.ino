#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <HardwareSerial.h>
#include <DFRobotDFPlayerMini.h>

// ==================== WIFI ====================
const char* ssid = "WIFI_LABO";
const char* password = "EpsiWis2018!";

// ==================== MQTT ====================
const char* mqtt_server = "172.16.118.56";
const char* mqtt_topic  = "bisik";

// ==================== SERVO ====================
Servo servo;
int servoPin = 12;

// ==================== OLED ====================
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_SDA 33
#define OLED_SCL 25
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// ==================== DFPLAYER ====================
HardwareSerial dfSerial(1);  // port série 1
DFRobotDFPlayerMini dfPlayer;

// ==================== MQTT CLIENT ====================
WiFiClient espClient;
PubSubClient client(espClient);

// ==================== CALLBACK MQTT ====================
void callback(char* topic, byte* payload, unsigned int length) {
    String jsonString;
    for (unsigned int i = 0; i < length; i++) jsonString += (char)payload[i];

    Serial.println("\n========== JSON REÇU ==========");
    Serial.println(jsonString);
    Serial.println("================================");

    DynamicJsonDocument doc(1024);
    if (deserializeJson(doc, jsonString)) {
        Serial.println("Erreur JSON !");
        return;
    }

    // ----------- AFFICHAGE MESSAGES -----------
    if (doc.containsKey("messages")) {
        JsonArray arr = doc["messages"];
        for (JsonObject msg : arr) {
            const char* txt = msg["message"];
            int duree = msg["duree"];

            Serial.print("AFFICHAGE : ");
            Serial.println(txt);

            display.clearDisplay();
            display.setCursor(0, 0);
            display.setTextSize(2);
            display.setTextColor(SSD1306_WHITE);
            display.println(txt);
            display.display();

            delay(duree);
        }
    }

    // ----------- SERVO -----------
    if (doc.containsKey("mouvements")) {
        JsonArray arr = doc["mouvements"];
        for (JsonObject mv : arr) {
            int angle = mv["angle"];
            int duree = mv["duree"];

            Serial.printf("SERVO -> %d° (%d ms)\n", angle, duree);
            servo.write(angle);
            delay(duree);
        }
    }

    // ----------- SON DFPLAYER -----------
    if (doc.containsKey("son")) {
        int num = doc["son"]; // numéro de fichier 1 à 5

        if (num >= 1 && num <= 5) {
            Serial.printf("Lecture du fichier Robot%d.mp3 à la racine\n", num);
            dfPlayer.play(num); // lit directement à la racine
            delay(300); // anti-crash DFPlayer
        } else {
            Serial.println("Numéro de fichier invalide !");
        }
    }

    // ----------- VOLUME DFPLAYER -----------
    if (doc.containsKey("volume")) {
        int vol = doc["volume"];
        dfPlayer.volume(vol);
        Serial.printf("VOLUME -> %d\n", vol);
    }
}

// ==================== RECONNEXION MQTT ====================
void reconnect() {
    while (!client.connected()) {
        Serial.println("Connexion MQTT...");
        if (client.connect("ESP32_Client")) {
            Serial.println("MQTT OK !");
            client.subscribe(mqtt_topic);
        } else {
            Serial.printf("Échec rc=%d → retry\n", client.state());
            delay(2000);
        }
    }
}

// ==================== SETUP ====================
void setup() {
    Serial.begin(115200);

    // ---- SERVO ----
    ESP32PWM::allocateTimer(0);
    servo.setPeriodHertz(50);
    servo.attach(servoPin, 500, 2400);

    // ---- OLED ----
    Wire.begin(OLED_SDA, OLED_SCL);
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println("Erreur OLED !");
        while (1);
    }
    display.clearDisplay();
    display.setTextSize(2);
    display.setTextColor(SSD1306_WHITE);
    display.setCursor(0, 0);
    display.println("READY");
    display.display();

    // ---- DFPLAYER ----
    dfSerial.begin(9600, SERIAL_8N1, 16, 17);
    Serial.println("Initialisation DFPlayer...");
    delay(1500);
    if (!dfPlayer.begin(dfSerial)) {
        Serial.println("!!! ERREUR DFPLAYER !!!");
    } else {
        dfPlayer.volume(20);
        Serial.println("DFPlayer OK !");
    }

    // ---- WIFI ----
    WiFi.begin(ssid, password);
    Serial.print("Connexion WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi OK");

    // ---- MQTT ----
    client.setServer(mqtt_server, 1883);
    client.setCallback(callback);
}

// ==================== LOOP ====================
void loop() {
    if (!client.connected()) reconnect();
    client.loop();
}
