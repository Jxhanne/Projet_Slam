<?php
// ===== Paramètres =====
$broker = "172.16.118.56";  // IP de ta VM
$topic = "labo/esp32";
$message = "Esp32 connecté";

// ===== Chemin complet de mosquitto_pub.exe =====
$mosquittoPath = '"C:\\Program Files\\mosquitto\\mosquitto_pub.exe"';
// ===== Construction de la commande =====
$command = "$mosquittoPath -h $broker -t $topic -m \"$message\" 2>&1";

// ===== Exécution =====
exec($command, $output, $status);

// ===== Affichage =====
if ($status === 0) {
    echo "Message publié : $message";
} else {
    echo "Erreur lors de la publication :\n";
    echo implode("\n", $output);
}
?>
