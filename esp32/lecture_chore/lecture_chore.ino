#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DFRobotDFPlayerMini.h>

// ===== WIFI =====
const char* ssid = "WIFI_LABO";
const char* password = "EpsiWis2018!";

// ===== MQTT =====
const char* mqtt_server = "172.16.118.56";
const int mqtt_port = 1883;
const char* mqtt_topic = "bisikJr";

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

  DynamicJsonDocument doc(2048);
  if (deserializeJson(doc, json)) return;

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

  if (doc.containsKey("mouvements")) {
    for (JsonObject mv : doc["mouvements"].as<JsonArray>()) {
      servo.write(mv["angle"]);
      delay(mv["duree"]);
    }
  }

  if (doc.containsKey("son")) {
    int num = doc["son"];
    int volume = doc["volume"] | 20;
    if (num >= 1 && num <= 5) {
      dfPlayer.volume(volume);
      dfPlayer.play(num);
    }
  }
}

// ===== MQTT RECONNECT =====
void reconnect() {
  while (!client.connected()) {
    if (client.connect("ESP32_BISIK")) {
      client.subscribe(mqtt_topic);
    } else {
      delay(2000);
    }
  }
}

// ===== SETUP =====
void setup() {
  Serial.begin(115200);

  ESP32PWM::allocateTimer(0);
  servo.setPeriodHertz(50);
  servo.attach(servoPin, 500, 2400);

  Wire.begin(OLED_SDA, OLED_SCL);
  display.begin(SSD1306_SWITCHCAPVCC, 0x3C);
  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("READY");
  display.display();

  Serial2.begin(9600, SERIAL_8N1, 16, 17);
  delay(1500);
  dfPlayer.begin(Serial2);
  dfPlayer.volume(20);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) delay(500);

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

// ===== LOOP =====
void loop() {
  if (!client.connected()) reconnect();
  client.loop();
}
