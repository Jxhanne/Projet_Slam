<?php
include "config.php";

if (!isset($_GET['id'])) die("ID de chorégraphie manquant");

$id = intval($_GET['id']);
if ($id <= 0) die("ID invalide !");

$jsonFile = __DIR__ . "/../json/choregraphie_$id.json";
if (!file_exists($jsonFile)) die("Fichier JSON introuvable !");

$data = json_decode(file_get_contents($jsonFile), true);
if ($data === null) die("JSON invalide !");

// Encode correctement le JSON
$jsonContent = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// ===== Crée un fichier temporaire =====
$tempFile = tempnam(sys_get_temp_dir(), 'mqtt_');
file_put_contents($tempFile, $jsonContent);

// Paramètres MQTT
$broker = "172.16.118.56";
$topic  = "bisik";
$mosquittoPath = '"C:\\Program Files\\mosquitto\\mosquitto_pub.exe"';

// Publie le fichier avec -f
$command = "$mosquittoPath -h $broker -t $topic -f \"$tempFile\" 2>&1";

// Exécution
exec($command, $output, $status);

// Supprimer le fichier temporaire
unlink($tempFile);

if ($status === 0) {
    header("Location: index.php?success=1");
    exit;
} else {
    echo "<pre>Erreur MQTT :\n" . implode("\n", $output) . "</pre>";
}
