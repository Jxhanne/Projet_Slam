<?php
require __DIR__ . '/vendor/autoload.php';
include "config.php";

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$pdo = new PDO(
    'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
    config::USER,
    config::PASSWORD
);

if (!isset($_GET['id'])) {
    die("ID de chorégraphie manquant");
}

$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID invalide");
}

// On fait attention aux horaires
$sql = "SELECT * FROM reglages LIMIT 1";
$req = $pdo->prepare($sql);
$req->execute();
$reglage = $req->fetch(PDO::FETCH_ASSOC);

$heure = date('H:i:s');
$jour  = date('N');

if ($jour >= 6) {

    $autorise = ($heure >= $reglage['debut_weekend']
        && $heure <= $reglage['fin_weekend']);

} else {

    $autorise = ($heure >= $reglage['debut_semaine']
        && $heure <= $reglage['fin_semaine']);
}

if (!$autorise) {
    header("Location: index.php?inactive=1");
    exit;
}

$jsonFile = __DIR__ . "/../json/choregraphie_$id.json";

if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable");
}

$data = json_decode(file_get_contents($jsonFile), true);
if ($data === null) {
    die("JSON invalide");
}

$jsonContent = json_encode(
    $data,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

// Param MQTT
$server   = "mqtt.latetedanslatoile.fr";
$port     = 1883;
$clientId = "PHP_BISIK_" . uniqid();
$username = "Epsi";
$password = "EpsiWis2018!";
$topic    = "bisikJr";

// Connexion au serveur MQTT
try {
    $mqtt = new MqttClient($server, $port, $clientId);

    $settings = (new ConnectionSettings())
        ->setUsername($username)
        ->setPassword($password)
        ->setConnectTimeout(5)
        ->setKeepAliveInterval(10);

    $mqtt->connect($settings, true);

    $mqtt->publish(
        $topic,
        $jsonContent,
        MqttClient::QOS_AT_LEAST_ONCE
    );

    $mqtt->disconnect();

} catch (Throwable $e) {
    die("Erreur MQTT : " . $e->getMessage());
}

header("Location: index.php?success=1");
exit;
