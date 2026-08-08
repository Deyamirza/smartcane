#include <WiFi.h>
#include <WebServer.h>
#include <WiFiManager.h>  
#include <PubSubClient.h> 
#include <TinyGPS++.h>
#include <HTTPClient.h>

// --- Definisi Pin Sensor Ultrasonik Dual HC-SR04 ---
// Sensor 1 (Bawah / Jangkauan Area Tanah)
const int trigPinBawah = 5;
const int echoPinBawah = 18;
float distanceBawahCm = 400.0;

// Sensor 2 (Tengah / Jangkauan Area Dada & Kepala)
const int trigPinTengah = 19;
const int echoPinTengah = 23;
float distanceTengahCm = 400.0;

// --- Definisi Pin Buzzer (Suara) ---
const int buzzerPin = 15; 

// --- Definisi Pin Motor Getar ---
const int vibeMotorPin = 14; 

// --- Definisi Pin Tombol SOS ---
const int buttonPin = 27; // Menggunakan internal pull-up

// --- Definisi Pin Modul GPS NEO-6M ---
#define RXD2 16  
#define TXD2 17  
const uint32_t GPSBaud = 9600;

TinyGPSPlus gps;

// --- Variabel State & Toggle SOS ---
bool sosActive = false;         
bool lastButtonState = HIGH;    
unsigned long lastDebounceTime = 0;
const unsigned long debounceDelay = 50; 

// --- Variabel Waktu untuk Beep Cepat SOS ---
unsigned long lastBeepToggle = 0;
bool fastBeepState = false;
const int fastBeepInterval = 100; 

// --- Timer Sampling Sensor Ultrasonik ---
unsigned long lastPingTime = 0;
const int pingInterval = 80; // Interval 80ms untuk pembacaan bersih kedua sensor

// --- Pengaturan Broker MQTT ---
const char* mqttServer = "98.95.57.110";
const int mqttPort = 1883;
const char* topicAlerts = "esp32/tracker/alerts";
const char* topicGPS    = "esp32/tracker/gps";

WiFiClient espClient;
PubSubClient mqttClient(espClient);
unsigned long lastMqttRetry = 0;
unsigned long lastGpsPublish = 0;

// Fungsi pembantu untuk membaca sensor ultrasonik secara bersih dengan batas waktu (timeout)
float readDistanceCm(int trigPin, int echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  
  long duration = pulseIn(echoPin, HIGH, 30000); // Timeout 30ms mencegah sistem macet/freeze
  if (duration == 0) return 400.0;
  return duration * 0.0343 / 2.0;
}

// Fungsi pembantu untuk mengelola koneksi MQTT di latar belakang (background)
void checkMQTTConnection() {
  if (!mqttClient.connected()) {
    if (millis() - lastMqttRetry > 15000) {
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
  
  // Inisialisasi Perangkat Perifer
  pinMode(trigPinBawah, OUTPUT); 
  pinMode(echoPinBawah, INPUT);   
  pinMode(trigPinTengah, OUTPUT); 
  pinMode(echoPinTengah, INPUT);   

  pinMode(buzzerPin, OUTPUT);
  digitalWrite(buzzerPin, LOW); 
  pinMode(vibeMotorPin, OUTPUT);
  digitalWrite(vibeMotorPin, LOW); 
  pinMode(buttonPin, INPUT_PULLUP);

  // Inisialisasi Serial Komunikasi GPS
  Serial2.begin(GPSBaud, SERIAL_8N1, RXD2, TXD2);

  Serial.println("\n--- Memulai WiFiManager ---");
  WiFiManager wm;
  
  // Timeout portal 2 menit. Jika tidak ada jaringan, lewati untuk mode lokal.
  wm.setConfigPortalTimeout(120);
  
  if (!wm.autoConnect("ESP32-SOS-Tracker")) {
    Serial.println("WiFi Portal Timeout. Berjalan dalam mode LOCAL-ONLY (Offline).");
  } else {
    Serial.println("WiFi berhasil terhubung!");
  }

  mqttClient.setServer(mqttServer, mqttPort);
}

void loop() {
  // ====================================================
  // KONDISI 1: KONTROLLER TOMBOL SOS (PRIORITAS TERTINGGI)
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

      // Pengiriman peringatan ke cloud (MQTT) di background
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
  // KONDISI 2: SAMPLING SENSOR ULTRASONIK DUAL (PEMBACAAN BERURUTAN)
  // ====================================================
  if (millis() - lastPingTime >= pingInterval) {
    lastPingTime = millis();
    
    // Baca Sensor 1 (Bawah)
    distanceBawahCm = readDistanceCm(trigPinBawah, echoPinBawah); // Menerima nilai jarak bawah
    
    // Jeda singkat agar gelombang suara sensor 1 mereda sebelum memicu sensor 2
    delayMicroseconds(5000); 
    
    // Baca Sensor 2 (Tengah)
    distanceTengahCm = readDistanceCm(trigPinTengah, echoPinTengah); // Menerima nilai jarak tengah
  }

  // ====================================================
  // KONDISI 3: KONTROLLER HARDWARE OUTPUT (BUZZER & MOTOR GETAR)
  // ====================================================
  static bool lastProximityState = false;

  if (sosActive) {
    // --- Mode SOS Aktif: Buzzer Beep Cepat, Motor Getar OFF ---
    digitalWrite(vibeMotorPin, LOW); 
    if (millis() - lastBeepToggle >= fastBeepInterval) {
      lastBeepToggle = millis();
      fastBeepState = !fastBeepState; 
      digitalWrite(buzzerPin, fastBeepState ? HIGH : LOW);
    }
  } 
  else {
    // --- Mode Deteksi Jarak (Proximity) Aktif: Cek Batas Jarak Kedua Sensor ---
    bool dangerBawah  = (distanceBawahCm > 0 && distanceBawahCm <= 100);
    bool dangerTengah = (distanceTengahCm > 0 && distanceTengahCm <= 100);

 
    // Jika ada rintangan bahaya (jarak <= 100cm)
    if (dangerBawah || dangerTengah) {
      digitalWrite(buzzerPin, HIGH);   
      // Memerintahkan Buzzer Menyala (HIGH) 
      digitalWrite(vibeMotorPin, HIGH); 
      // Memerintahkan Motor Getar Menyala (HIGH)

    
      if (!lastProximityState) { 
        if (WiFi.status() == WL_CONNECTED && mqttClient.connected()) {
          float minDist = min(distanceBawahCm, distanceTengahCm);
          String pos = (dangerBawah && dangerTengah) ? "BOTH" : (dangerTengah ? "MIDDLE" : "BOTTOM");
          String payload = "{\"status\":\"PROXIMITY_ALARM\",\"distance\":" + String(minDist) + 
                           ",\"position\":\"" + pos + 
                           "\",\"dist_bawah\":" + String(distanceBawahCm) + 
                           ",\"dist_tengah\":" + String(distanceTengahCm) + "}";
          mqttClient.publish(topicAlerts, payload.c_str());
        }
        lastProximityState = true;
      }
    } else {
      // Zona Aman (Tidak ada rintangan)
      digitalWrite(buzzerPin, LOW);     
      digitalWrite(vibeMotorPin, LOW);  
      
      // Transisi dari zona bahaya ke aman: laporkan status aman ke cloud
      if (lastProximityState) {
        if (WiFi.status() == WL_CONNECTED && mqttClient.connected()) {
          float minSafeDist = min(distanceBawahCm, distanceTengahCm);
          String payload = "{\"status\":\"DISTANCE_UPDATE\",\"distance\":" + String(minSafeDist) + 
                           ",\"dist_bawah\":" + String(distanceBawahCm) + 
                           ",\"dist_tengah\":" + String(distanceTengahCm) + "}";
          mqttClient.publish(topicAlerts, payload.c_str());
        }
      }
      lastProximityState = false;
    }
  }

  // ====================================================
  // KONDISI 4: TUGAS LATAR BELAKANG (LOGIKA GPS & JARINGAN)
  // ====================================================
  
  // Membaca data serial GPS secara terus-menerus
  while (Serial2.available() > 0) {
    char c = Serial2.read();
    gps.encode(c);
  }

  // Kelola koneksi MQTT & rekonfigurasi jika WiFi terhubung
  if (WiFi.status() == WL_CONNECTED) {
    checkMQTTConnection();
    mqttClient.loop();
    
    // Sinkronisasi data lokasi GPS ke cloud setiap 10 detik
    if (millis() - lastGpsPublish > 10000) {
      lastGpsPublish = millis();
      if (gps.location.isValid() && mqttClient.connected()) {
        String gpsPayload = "{\"lat\":" + String(gps.location.lat(), 6) + 
                            ",\"lng\":" + String(gps.location.lng(), 6) + "}";
        mqttClient.publish(topicGPS, gpsPayload.c_str());
      }
    }

    // Sinkronisasi pembaruan jarak berkala setiap 10 detik (hanya saat aman)
    static unsigned long lastDistancePublish = 0;
    if (millis() - lastDistancePublish > 10000) {
      lastDistancePublish = millis();
      if (!sosActive && !lastProximityState && mqttClient.connected()) {
        float minSafeDist = min(distanceBawahCm, distanceTengahCm);
        String payload = "{\"status\":\"DISTANCE_UPDATE\",\"distance\":" + String(minSafeDist) +  
                         ",\"dist_bawah\":" + String(distanceBawahCm) + 
                         ",\"dist_tengah\":" + String(distanceTengahCm) + "}";
        mqttClient.publish(topicAlerts, payload.c_str());
      }
    }
  }

  // Cetak diagnostik sistem ke Serial Monitor setiap 4 detik
  static unsigned long lastPrint = 0;
  if (millis() - lastPrint > 4000) {
    lastPrint = millis();
    Serial.print("[SYSTEM OK] Dist Bawah: "); 
    Serial.print(distanceBawahCm); 
    Serial.print("cm | Dist Tengah: ");
    Serial.print(distanceTengahCm);
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
