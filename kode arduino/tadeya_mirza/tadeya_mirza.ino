#include <WiFi.h>
#include <WebServer.h>
#include <WiFiManager.h>  // Install via Library Manager (by tzapu)
#include <PubSubClient.h> // Install via Library Manager (by Nick O'Leary)
#include <TinyGPS++.h>

// --- HC-SR04 Pin Definitions ---
const int trigPin = 5;
const int echoPin = 18;
long duration;
float distanceCm;

// --- Buzzer Pin Definition ---
const int buzzerPin = 15; 

// --- Vibration Motor Pin Definition ---
const int vibeMotorPin = 14; 

// --- Button Pin Definition ---
const int buttonPin = 27; // Internal pull-up

// --- NEO-6M GPS Pins ---
#define RXD2 16  
#define TXD2 17  
const uint32_t GPSBaud = 9600;

TinyGPSPlus gps;
 

// --- SOS Toggle Variables ---
bool sosActive = false;         
bool lastButtonState = HIGH;    
unsigned long lastDebounceTime = 0;
const unsigned long debounceDelay = 50; 

// --- Timing variables for the SOS rapid beep ---
unsigned long lastBeepToggle = 0;
bool fastBeepState = false;
const int fastBeepInterval = 100; 

// --- Ultrasonic Sampling Timer ---
unsigned long lastPingTime = 0;
const int pingInterval = 60; // Read distance every 60ms for smooth real-time response

// --- MQTT Broker Settings ---
const char* mqttServer = "broker.emqx.io"; 
const int mqttPort = 1883;
const char* topicAlerts = "esp32/tracker/alerts";
const char* topicGPS    = "esp32/tracker/gps";

WiFiClient espClient;
PubSubClient mqttClient(espClient);
unsigned long lastMqttRetry = 0;
unsigned long lastGpsPublish = 0;

// Background background connection helper
void checkMQTTConnection() {
  if (!mqttClient.connected()) {
    if (millis() - lastMqttRetry > 15000) { // Dropped to 15 seconds to stay out of the way
      lastMqttRetry = millis();
      Serial.print("MQTT Disconnected. Retrying in background... ");
      String clientId = "ESP32Client-" + String(WiFi.macAddress());
      if (mqttClient.connect(clientId.c_str())) {
        Serial.println("CONNECTED!");
      } else {
        Serial.print("failed, rc=");
        Serial.println(mqttClient.state());
      }
    }
  }
}

void setup() {
  Serial.begin(115200);
  
  // Initialize Peripherals Immediately
  pinMode(trigPin, OUTPUT); 
  pinMode(echoPin, INPUT);   
  pinMode(buzzerPin, OUTPUT);
  digitalWrite(buzzerPin, LOW); 
  pinMode(vibeMotorPin, OUTPUT);
  digitalWrite(vibeMotorPin, LOW); 
  pinMode(buttonPin, INPUT_PULLUP);

  // Initialize GPS serial
  Serial2.begin(GPSBaud, SERIAL_8N1, RXD2, TXD2);

  Serial.println("\n--- Initializing WiFiManager ---");
  WiFiManager wm;
  
  // 2-minute portal timeout. If no network, it bypasses to protect local usage.
  wm.setConfigPortalTimeout(120);
  
  if (!wm.autoConnect("ESP32-SOS-Tracker")) {
    Serial.println("WiFi Portal Timeout. Running in LOCAL-ONLY mode.");
  } else {
    Serial.println("WiFi connected successfully!");
  }

  mqttClient.setServer(mqttServer, mqttPort);
}

void loop() {
  // ====================================================
  // CONDITION 1: STANDALONE BUTTON / SOS CONTROLLER (HIGHEST PRIORITY)
  // ====================================================
  bool currentButtonReading = digitalRead(buttonPin);
  if (currentButtonReading != lastButtonState) {
    lastDebounceTime = millis();
  }

  if ((millis() - lastDebounceTime) > debounceDelay) {
    static bool buttonPressed = false;
    if (currentButtonReading == LOW && !buttonPressed) {
      sosActive = !sosActive; 
      buttonPressed = true;
      Serial.print("SOS TOGGLED: ");
      Serial.println(sosActive ? "ACTIVE" : "DISABLED");

      // Background cloud alert
      if (WiFi.status() == WL_CONNECTED && mqttClient.connected()) {
        String payload = sosActive ? "{\"status\":\"SOS_ACTIVE\"}" : "{\"status\":\"SOS_DEACTIVATED\"}";
        mqttClient.publish(topicAlerts, payload.c_str());
      }
    } else if (currentButtonReading == HIGH) {
      buttonPressed = false;
    }
  }
  lastButtonState = currentButtonReading;

  // ====================================================
  // CONDITION 2: ULTRASONIC SENSOR SAMPLING (TIMED FOR CLEAN READS)
  // ====================================================
  if (millis() - lastPingTime >= pingInterval) {
    lastPingTime = millis();
    
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    
    duration = pulseIn(echoPin, HIGH, 30000); // 30ms timeout prevents freezing if sensor unplugged
    distanceCm = duration * 0.0343 / 2;
  }

  // ====================================================
  // CONDITION 3: OUTPUT HARDWARE CONTROLLER (ACTS INSTANTLY)
  // ====================================================
  static bool lastProximityState = false;

  if (sosActive) {
    // --- SOS Mode Active: Fast Beep Override, Motor OFF ---
    digitalWrite(vibeMotorPin, LOW); 
    if (millis() - lastBeepToggle >= fastBeepInterval) {
      lastBeepToggle = millis();
      fastBeepState = !fastBeepState; 
      digitalWrite(buzzerPin, fastBeepState ? HIGH : LOW);
    }
  } 
  else {
    // --- Proximity Mode Active: Check Distance Constraints ---
    if (distanceCm > 0 && distanceCm <= 100) {
      digitalWrite(buzzerPin, HIGH);    
      digitalWrite(vibeMotorPin, HIGH); 
      
      if (!lastProximityState) { 
        if (WiFi.status() == WL_CONNECTED && mqttClient.connected()) {
          String payload = "{\"status\":\"PROXIMITY_ALARM\",\"distance\":" + String(distanceCm) + "}";
          mqttClient.publish(topicAlerts, payload.c_str());
        }
        lastProximityState = true;
      }
    } else {
      // Safe zone
      digitalWrite(buzzerPin, LOW);     
      digitalWrite(vibeMotorPin, LOW);  
      lastProximityState = false;
    }
  }

  // ====================================================
  // CONDITION 4: BACKGROUND TASKS (GPS & NETWORK LOGIC)
  // ====================================================
  
  // Read GPS serial buffer continuously
  while (Serial2.available() > 0) {
    char c=Serial2.read();
    gps.encode(c);
  }

  // Handle MQTT client internals & reconnects safely if WiFi is up
  if (WiFi.status() == WL_CONNECTED) {
    checkMQTTConnection();
    mqttClient.loop();
    
    // Periodic 10-second GPS cloud sync
    if (millis() - lastGpsPublish > 10000) {
      lastGpsPublish = millis();
      if (gps.location.isValid() && mqttClient.connected()) {
        String gpsPayload = "{\"lat\":" + String(gps.location.lat(), 6) + 
                            ",\"lng\":" + String(gps.location.lng(), 6) + "}";
        mqttClient.publish(topicGPS, gpsPayload.c_str());
      }
    }
  }

  // Quick Serial Terminal diagnostics printout every 4 seconds
  static unsigned long lastPrint = 0;
  if (millis() - lastPrint > 4000) {
    lastPrint = millis();
    Serial.print("[SYSTEM OK] Distance: "); 
    Serial.print(distanceCm); 
    Serial.print("cm");
    
    // Status SOS
    Serial.print(" | SOS: "); 
    Serial.print(sosActive ? "ON" : "OFF");
    
    // Status Motor Getar
    bool vibeState = digitalRead(vibeMotorPin);
    Serial.print(" | Vibe: "); 
    Serial.print(vibeState ? "ON" : "OFF");

    // Status Buzzer
    bool buzzerState = digitalRead(buzzerPin);
    Serial.print(" | Buzz: "); 
    Serial.print(buzzerState ? "ON" : "OFF");
    
    // Status GPS
    Serial.print(" | GPS: ");
    if (gps.charsProcessed() == 0) {
      Serial.print("NO_CONN (TIDAK ADA DATA! Periksa Kabel: GPS TX -> ESP32 RX2/GPIO16)");
    } else if (!gps.location.isValid()) {
      Serial.print("NO_FIX (Sats: ");
      Serial.print(gps.satellites.value());
      Serial.print(", Chars RX: ");
      Serial.print(gps.charsProcessed());
      Serial.print(", Checksum Fail: ");
      Serial.print(gps.failedChecksum());
      Serial.print(")");
    } else {
      Serial.print("FIX (Lat: ");
      Serial.print(gps.location.lat(), 6);
      Serial.print(", Lng: ");
      Serial.print(gps.location.lng(), 6);
      Serial.print(", Sats: "); 
      Serial.print(gps.satellites.value());
      Serial.print(")");
    }
    
    Serial.println(WiFi.status() == WL_CONNECTED ? " | Net: ONLINE" : " | Net: OFFLINE");
  }
}