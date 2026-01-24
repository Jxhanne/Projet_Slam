#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DFRobotDFPlayerMini.h>

// ===== WIFI =====
const char* ssid = "Livebox-5AE0";
const char* password = "Johanne44";

// ===== MQTT =====
const char* mqtt_server = "mqtt.latetedanslatoile.fr";
const char* mqtt_user   = "Epsi";
const char* mqtt_pass   = "EpsiWis2018!";
const char* mqtt_topic  = "bisikJr";

// ===== SERVO =====
Servo servo;
const int servoPin = 12;

// ===== OLED =====
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_SDA 33
#define OLED_SCL 25
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// ===== DFPLAYER =====
DFRobotDFPlayerMini dfPlayer;

// ===== MQTT =====
WiFiClient espClient;
PubSubClient client(espClient);

// ===== CALLBACK MQTT =====
void callback(char* topic, byte* payload, unsigned int length) {
  String json;
  for (unsigned int i = 0; i < length; i++) {
    json += (char)payload[i];
  }

  Serial.println("\nJSON reçu :");
  Serial.println(json);

  DynamicJsonDocument doc(2048);
  if (deserializeJson(doc, json)) {
    Serial.println("Erreur JSON");
    return;
  }

  // --- OLED ---
  if (doc.containsKey("messages")) {
    for (JsonObject msg : doc["messages"].as<JsonArray>()) {
      display.clearDisplay();
      display.setTextSize(2);
      display.setTextColor(SSD1306_WHITE);
      display.setCursor(0, 0);
      display.println(msg["message"].as<const char*>());
      display.display();
      delay(msg["duree"]);
    }
  }

  // --- SERVO ---
  if (doc.containsKey("mouvements")) {
    for (JsonObject mv : doc["mouvements"].as<JsonArray>()) {
      servo.write(mv["angle"]);
      delay(mv["duree"]);
    }
  }

  // --- SON ---
  if (doc.containsKey("son")) {
    int num = doc["son"];
    int volume = doc["volume"] | 20;

    if (num >= 1 && num <= 5) {
      dfPlayer.volume(volume);
      delay(100);
      dfPlayer.play(num);
      delay(300);
    }
  }
}

// ===== MQTT RECONNECT =====
void reconnect() {
  while (!client.connected()) {
    Serial.print("Connexion MQTT...");
    if (client.connect("ESP32_BISIK", mqtt_user, mqtt_pass)) {
      Serial.println(" OK");
      client.subscribe(mqtt_topic);
    } else {
      Serial.print(" échec rc=");
      Serial.println(client.state());
      delay(2000);
    }
  }
}

// ===== SETUP =====
void setup() {
  Serial.begin(115200);

  // Servo
  ESP32PWM::allocateTimer(0);
  servo.setPeriodHertz(50);
  servo.attach(servoPin, 500, 2400);

  // OLED
  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) while (true);
  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("READY");
  display.display();

  // DFPlayer
  Serial2.begin(9600, SERIAL_8N1, 16, 17);
  delay(1500);
  if (!dfPlayer.begin(Serial2)) {
    Serial.println("DFPlayer NON détecté");
  } else {
    dfPlayer.volume(20);
  }

  // WiFi
  WiFi.begin(ssid, password);
  Serial.print("WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println(" connecté");

  // MQTT
  client.setServer(mqtt_server, 1883);
  client.setCallback(callback);
}

// ===== LOOP =====
void loop() {
  if (!client.connected()) reconnect();
  client.loop();
}
