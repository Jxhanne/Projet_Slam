<?php
include '../header.php';
include "../config.php";

$message   = $_POST['message'];
$angles    = $_POST['angle'];
$durations = $_POST['duration'];

$pdo = new PDO(
    'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
    config::USER,
    config::PASSWORD
);


$sql = "INSERT INTO choregraphies (message) VALUES ('$message')";
$pdo->query($sql);

// Récupération de l'ID de la chore créée
$chore_id = $pdo->lastInsertId();

foreach ($angles as $i => $angle) {
    $duration = $durations[$i];

    $sql = "INSERT INTO mouvements (chore_id, angle, duration)
            VALUES ($chore_id, $angle, $duration)";
    $pdo->query($sql);
}

// CRÉATION DU JSON
$data = [
    "id" => $chore_id,
    "message" => $message,
    "movements" => []
];

foreach ($angles as $i => $angle) {
    $data["movements"][] = [
        "angle" => $angle,
        "duration" => $durations[$i]
    ];
}

$json_path = __DIR__ . "/../../json/" . $chore_id . ".json";
file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT));


header("Location: ../confirm.php?id=" . $chore_id);
exit;

include "../footer.php";
?>
