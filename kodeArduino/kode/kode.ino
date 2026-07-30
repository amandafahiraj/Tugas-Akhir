#include <WiFi.h>
#include <WiFiManager.h>
#include <PubSubClient.h>
#include <TinyGPSPlus.h>
#include <SPI.h>
#include <SD.h>

// =========================
// GPS
// =========================
HardwareSerial gpsSerial(2);
TinyGPSPlus gps;

#define RXD2 16
#define TXD2 17

// =========================
// SD CARD (SPI)
// =========================
#define SD_CS    5    // CS modul SD ke D5/GPIO5
#define SD_SCK   18
#define SD_MISO  19
#define SD_MOSI  23

SPIClass sdSPI(VSPI);

// =========================
// MQTT
// =========================
const char* deviceId = "esp32-gps-01";
const char* mqttBroker = "100.50.177.16"; //// ip wajib ganti kalau ganti jaringan
const uint16_t mqttPort = 1883;
const char* mqttTopic = "trackhub/gps";
const char* mqttUser = "";
const char* mqttPassword = "";

WiFiClient wifiClient;
PubSubClient mqttClient(wifiClient);

const unsigned long publishIntervalMs = 5000;
const bool debugRawNmea = false;

// =========================
// VARIABLE
// =========================
String latestNmea = "";
String currentNmea = "";

unsigned long lastPostMs = 0;
unsigned long lastWifiReconnectMs = 0;
unsigned long lastMqttReconnectMs = 0;
unsigned long lastSdRetryMs = 0;
bool sdReady = false;

const char* queuePath = "/gps_queue.json";
const char* tempQueuePath = "/gps_queue_tmp.json";
const unsigned long wifiReconnectIntervalMs = 15000;
const unsigned long mqttReconnectIntervalMs = 5000;
const unsigned long sdRetryIntervalMs = 30000;

// =====================================================
// SETUP
// =====================================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("\nESP32 GPS Tracker Starting...");
  Serial.print("MQTT Broker: ");
  Serial.print(mqttBroker);
  Serial.print(":");
  Serial.println(mqttPort);
  Serial.print("MQTT Topic: ");
  Serial.println(mqttTopic);

  gpsSerial.begin(9600, SERIAL_8N1, RXD2, TXD2);
  Serial.println("GPS Serial Started");

  // Paksa CS HIGH dulu sebelum init apapun
  pinMode(SD_CS, OUTPUT);
  digitalWrite(SD_CS, HIGH);
  delay(100);

  initSDCard();

  // ===== untuk membersihkan kartu memorinya =====
  // if (sdReady && SD.exists(queuePath)) {
  //   SD.remove(queuePath);
  //   Serial.println("gps_queue.json berhasil dihapus.");
  // } else {
  //   Serial.println("gps_queue.json tidak ditemukan.");
  // }
  // =============================
  // ===== untuk cek isi kartu memori ======
  if (SD.exists(queuePath)) {
    Serial.println("===== ISI gps_queue.json =====");

    File file = SD.open(queuePath, FILE_READ);

    while (file.available()) {
        Serial.write(file.read());
    }

    file.close();

    Serial.println();
    Serial.println("===== SELESAI =====");
  }


  WiFiManager wifiManager;
  wifiManager.setConfigPortalTimeout(180);

  if (!wifiManager.autoConnect("ESP32-GPS-Setup")) {
    Serial.println("Gagal connect WiFi. Data akan disimpan ke SD.");
    WiFi.disconnect(true);
  } else {
    Serial.println("WiFi Connected: " + WiFi.localIP().toString());
  }

  mqttClient.setServer(mqttBroker, mqttPort);
  mqttClient.setBufferSize(2048);

  Serial.println("Waiting GPS signal...");
}

// =====================================================
// LOOP
// =====================================================
void loop() {
  maintainWiFi();
  maintainMQTT();
  mqttClient.loop();
  maintainSDCard();

  while (gpsSerial.available()) {
    char c = gpsSerial.read();

    if (debugRawNmea) Serial.write(c);

    gps.encode(c);

    if (c == '\n') {
      currentNmea.trim();
      if (currentNmea.length() > 0) latestNmea = currentNmea;
      currentNmea = "";
    } else if (c != '\r') {
      currentNmea += c;
      if (currentNmea.length() > 1800)
        currentNmea = currentNmea.substring(currentNmea.length() - 1800);
    }
  }

  if (millis() - lastPostMs >= publishIntervalMs) {
    printGPSData();

    bool gpsLocked = gps.location.isValid();
    String payload = buildGpsPayload(false);

    if (gpsLocked) {
      if (sendPayload(payload)) {
        syncQueuedPayloads();
      } else {
        enqueueJsonPayload(buildGpsPayload(true));
      }
    } else {
      Serial.println("GPS belum lock satelit. Mengirim heartbeat status ke MQTT...");
      sendPayload(payload);
    }

    lastPostMs = millis();
  }
}

// =====================================================
// WIFI RECONNECT
// =====================================================
void maintainWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  if (millis() - lastWifiReconnectMs < wifiReconnectIntervalMs) return;
  lastWifiReconnectMs = millis();
  Serial.println("WiFi reconnecting...");
  WiFi.reconnect();
}

// =====================================================
// INIT SD CARD — FIXED
// =====================================================
void initSDCard() {
  sdReady = false;

  Serial.println("\n--- Initializing SD Card ---");
  Serial.printf("Pins: CS=%d SCK=%d MISO=%d MOSI=%d\n", SD_CS, SD_SCK, SD_MISO, SD_MOSI);

  pinMode(SD_CS, OUTPUT);
  digitalWrite(SD_CS, HIGH);
  pinMode(SD_MISO, INPUT_PULLUP);
  pinMode(SD_MOSI, OUTPUT);
  pinMode(SD_SCK, OUTPUT);
  delay(300);

  const uint32_t freqs[] = { 100000, 400000, 1000000, 4000000 };

  for (int i = 0; i < 4; i++) {
    Serial.printf("Trying freq: %lu Hz...\n", freqs[i]);

    SD.end();
    sdSPI.end();
    delay(150);

    digitalWrite(SD_CS, HIGH);
    delay(50);
    sdSPI.begin(SD_SCK, SD_MISO, SD_MOSI, SD_CS);
    delay(150);

    if (SD.begin(SD_CS, sdSPI, freqs[i], "/sd", 5)) {
      uint8_t cardType = SD.cardType();

      if (cardType == CARD_NONE) {
        Serial.println("SPI OK tapi kartu tidak terdeteksi. Cek fisik kartu!");
        SD.end();
        continue;
      }

      sdReady = true;
      Serial.printf("SD OK! Type: %d, Size: %lu MB\n",
        cardType, (uint32_t)(SD.cardSize() / (1024 * 1024)));

      File testFile = SD.open("/sd_test.txt", FILE_WRITE);
      if (!testFile) {
        Serial.println("SD terbaca, tapi gagal tes tulis. Coba format ulang FAT32.");
        sdReady = false;
        SD.end();
        continue;
      }

      testFile.println("SD write OK");
      testFile.close();
      SD.remove("/sd_test.txt");
      Serial.println("Tes tulis SD berhasil.");

      sdReady = true;
      Serial.println("SD Card siap dipakai.");
      return;
    }

    Serial.printf("Gagal di freq %lu\n", freqs[i]);
    delay(300);
  }

  Serial.println("!!! SD GAGAL SEMUA PERCOBAAN !!!");
  Serial.println("Checklist:");
  Serial.println("  1. CS benar-benar ke GPIO 2 / D2?");
  Serial.println("  2. Kartu format FAT32?");
  Serial.println("  3. Kalau modul SD ada regulator/level shifter, coba VCC ke 5V.");
  Serial.println("     Kalau modul SD polos tanpa regulator, VCC wajib 3.3V.");
  Serial.println("  4. GND tersambung?");
  Serial.println("  5. Pastikan MISO modul ke GPIO19, MOSI modul ke GPIO23.");
}

void maintainSDCard() {
  if (sdReady) return;
  if (millis() - lastSdRetryMs < sdRetryIntervalMs) return;
  lastSdRetryMs = millis();
  Serial.println("Retry init SD Card...");
  initSDCard();
}

// ensureCsvHeader removed

// =====================================================
// PRINT GPS
// =====================================================
void printGPSData() {
  Serial.println("\n===== GPS DATA =====");
  if (gps.location.isValid()) {
    Serial.printf("Lat: %.7f  Lng: %.7f\n",
      gps.location.lat(), gps.location.lng());
  } else {
    Serial.println("Location: INVALID (belum lock satelit)");
  }
  if (gps.speed.isValid())     Serial.printf("Speed: %.2f km/h\n", gps.speed.kmph());
  if (gps.altitude.isValid())  Serial.printf("Alt: %.2f m\n", gps.altitude.meters());
  if (gps.satellites.isValid()) Serial.printf("Sats: %lu\n", gps.satellites.value());
  if (gps.hdop.isValid())      Serial.printf("HDOP: %.2f\n", gps.hdop.hdop());
  Serial.println("====================");
}


// =====================================================
// BUILD GPS PAYLOAD
// =====================================================
String buildGpsPayload(bool offline) {
  String p = "{";
  p += "\"device_id\":\"" + String(deviceId) + "\",";
  p += "\"latitude\":" + jsonNumber(gps.location.isValid(), gps.location.lat(), 7) + ",";
  p += "\"longitude\":" + jsonNumber(gps.location.isValid(), gps.location.lng(), 7) + ",";
  p += "\"altitude_m\":" + jsonNumber(gps.altitude.isValid(), gps.altitude.meters(), 2) + ",";
  p += "\"speed_kmph\":" + jsonNumber(gps.speed.isValid(), gps.speed.kmph(), 2) + ",";
  p += "\"satellites\":" + jsonInteger(gps.satellites.isValid(), gps.satellites.value()) + ",";
  p += "\"hdop\":" + jsonNumber(gps.hdop.isValid(), gps.hdop.hdop(), 2) + ",";
  p += "\"raw_nmea\":\"" + escapeJson(latestNmea) + "\",";
  p += "\"recorded_at\":" + jsonDateTime() + ",";
  p += "\"offline\":" + String(offline ? "true" : "false");
  p += "}";
  return p;
}

// =====================================================
// MQTT CONNECTION
// =====================================================
void maintainMQTT() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  if (mqttClient.connected()) {
    return;
  }

  if (millis() - lastMqttReconnectMs < mqttReconnectIntervalMs) {
    return;
  }

  lastMqttReconnectMs = millis();
  connectMQTT();
}

bool connectMQTT() {
  if (WiFi.status() != WL_CONNECTED) {
    return false;
  }

  String clientId = String(deviceId) + "-" + String((uint32_t)ESP.getEfuseMac(), HEX);
  Serial.print("MQTT connecting as ");
  Serial.println(clientId);

  bool connected;
  if (strlen(mqttUser) > 0) {
    connected = mqttClient.connect(clientId.c_str(), mqttUser, mqttPassword);
  } else {
    connected = mqttClient.connect(clientId.c_str());
  }

  if (connected) {
    Serial.println("MQTT connected.");
  } else {
    Serial.print("MQTT connect failed, rc=");
    Serial.println(mqttClient.state());
  }

  return connected;
}

// =====================================================
// SEND PAYLOAD
// =====================================================
bool sendPayload(String payload) {
  Serial.println("\n--- Publishing to MQTT ---");

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi offline. Simpan ke SD.");
    return false;
  }

  if (!mqttClient.connected() && !connectMQTT()) {
    Serial.println("MQTT offline. Simpan ke SD.");
    return false;
  }

  bool ok = mqttClient.publish(mqttTopic, payload.c_str(), false);
  Serial.println(ok ? "MQTT publish OK." : "MQTT publish failed.");
  return ok;
}

// =====================================================
// OFFLINE QUEUE
// =====================================================
void enqueueJsonPayload(String payload) {
  if (!sdReady) {
    initSDCard();
    if (!sdReady) {
      Serial.println("SD tidak siap, data hilang.");
      return;
    }
  }

  File file = SD.open(queuePath, FILE_APPEND);
  if (!file) {
    Serial.println("Gagal buka queue JSON.");
    sdReady = false;
    return;
  }

  file.println(payload);
  file.close();
  Serial.println("Data disimpan ke SD offline queue.");
}

void syncQueuedPayloads() {
  if (!sdReady || !SD.exists(queuePath)) return;

  File file = SD.open(queuePath, FILE_READ);
  if (!file) return;

  if (SD.exists(tempQueuePath)) SD.remove(tempQueuePath);

  File temp = SD.open(tempQueuePath, FILE_WRITE);
  if (!temp) { file.close(); return; }

  int sent = 0, kept = 0;

  while (file.available()) {
    String row = file.readStringUntil('\n');
    row.trim();

    if (row.length() == 0) continue;

    Serial.println("========== DATA DARI SD ==========");
    Serial.print("Device     : "); Serial.println(getJsonValue(row, "device_id"));
    Serial.print("Latitude   : "); Serial.println(getJsonValue(row, "latitude"));
    Serial.print("Longitude  : "); Serial.println(getJsonValue(row, "longitude"));
    Serial.print("Altitude   : "); Serial.println(getJsonValue(row, "altitude_m"));
    Serial.print("Speed      : "); Serial.println(getJsonValue(row, "speed_kmph"));
    Serial.print("Satellite  : "); Serial.println(getJsonValue(row, "satellites"));
    Serial.print("HDOP       : "); Serial.println(getJsonValue(row, "hdop"));
    Serial.print("Offline    : "); Serial.println(getJsonValue(row, "offline"));
    Serial.print("Recorded   : "); Serial.println(getJsonValue(row, "recorded_at"));
    Serial.println("==================================");

    bool publishSuccess = false;

    // Hanya mencoba mengirim jika WiFi dan MQTT masih terhubung.
    // Jika koneksi putus di tengah jalan, sisa data langsung disimpan ke temp tanpa mencoba kirim (agar tidak blocking).
    if (WiFi.status() == WL_CONNECTED && mqttClient.connected()) {
      if (sendPayload(row)) {
        publishSuccess = true;
        sent++;
        delay(250);
      }
    } else {
      Serial.println("Koneksi terputus saat sinkronisasi. Sisa data ditunda.");
    }

    if (!publishSuccess) {
      temp.println(row);
      kept++;
    }
  }

  file.close();
  temp.close();
  SD.remove(queuePath);

  if (kept > 0) {
    SD.rename(tempQueuePath, queuePath);
  } else {
    SD.remove(tempQueuePath);
  }

  Serial.printf("Sync: sent=%d, remaining=%d\n", sent, kept);
}

// csvRowToPayload, parseCsvRow, csvJsonNumber, escapeCsv, csvDateTime removed

String jsonNumber(bool valid, double value, int decimals) {
  return valid ? String(value, decimals) : "null";
}

String jsonInteger(bool valid, unsigned long value) {
  return valid ? String(value) : "null";
}

String jsonDateTime() {
  if (!gps.date.isValid() || !gps.time.isValid()) return "null";
  char buf[28];
  snprintf(buf, sizeof(buf), "\"%04d-%02d-%02dT%02d:%02d:%02dZ\"",
    gps.date.year(), gps.date.month(), gps.date.day(),
    gps.time.hour(), gps.time.minute(), gps.time.second());
  return String(buf);
}

String escapeJson(String value) {
  value.replace("\\", "\\\\");
  value.replace("\"", "\\\"");
  value.replace("\n", "\\n");
  value.replace("\r", "\\r");
  return value;
}

String getJsonValue(String json, String key) {
  int keyIndex = json.indexOf("\"" + key + "\"");
  if (keyIndex == -1) return "null";
  
  int colonIndex = json.indexOf(":", keyIndex + key.length() + 2);
  if (colonIndex == -1) return "null";
  
  int startValueIndex = colonIndex + 1;
  while (startValueIndex < json.length() && json[startValueIndex] == ' ') {
    startValueIndex++;
  }
  
  if (startValueIndex >= json.length()) return "null";
  
  if (json[startValueIndex] == '"') {
    startValueIndex++;
    int endQuoteIndex = json.indexOf("\"", startValueIndex);
    if (endQuoteIndex == -1) return "null";
    return json.substring(startValueIndex, endQuoteIndex);
  } else {
    int commaIndex = json.indexOf(",", startValueIndex);
    int braceIndex = json.indexOf("}", startValueIndex);
    int endIndex = -1;
    if (commaIndex != -1 && braceIndex != -1) {
      endIndex = min(commaIndex, braceIndex);
    } else if (commaIndex != -1) {
      endIndex = commaIndex;
    } else {
      endIndex = braceIndex;
    }
    
    if (endIndex == -1) return "null";
    String val = json.substring(startValueIndex, endIndex);
    val.trim();
    return val;
  }
}