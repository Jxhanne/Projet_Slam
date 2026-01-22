<?php
include_once "../config.php";

$pdo = new PDO(
    'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
    config::USER,
    config::PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$stmt = $pdo->prepare("INSERT INTO choregraphies (date_creation) VALUES (NOW())");
$stmt->execute();
$chore_id = $pdo->lastInsertId();

$messages_json = [];
if (!empty($_POST['message']) && !empty($_POST['duree_message'])) {
    $stmtMessage = $pdo->prepare("INSERT INTO messages (chore_id, message, duree) VALUES (:chore_id, :message, :duree)");
    foreach ($_POST['message'] as $index => $msg) {
        $message = $msg;
        $duree   = $_POST['duree_message'][$index] ?? null;
        if (!empty($message) && is_numeric($duree)) {
            $stmtMessage->bindValue(':chore_id', $chore_id, PDO::PARAM_INT);
            $stmtMessage->bindValue(':message',  $message,  PDO::PARAM_STR);
            $stmtMessage->bindValue(':duree',    $duree,    PDO::PARAM_INT);
            $stmtMessage->execute();

            $messages_json[] = [
                'message' => $message,
                'duree'   => (int)$duree
            ];
        }
    }
}
$mouvements_json = [];
if (!empty($_POST['angle']) && !empty($_POST['duree_angle'])) {
    $stmtMouvement = $pdo->prepare("INSERT INTO mouvements (chore_id, angle, duree) VALUES (:chore_id, :angle, :duree)");
    foreach ($_POST['angle'] as $index => $ang) {
        $angle = $ang;
        $duree = $_POST['duree_angle'][$index] ?? null;
        if (is_numeric($angle) && is_numeric($duree)) {
            $stmtMouvement->bindValue(':chore_id', $chore_id, PDO::PARAM_INT);
            $stmtMouvement->bindValue(':angle',    $angle,    PDO::PARAM_INT);
            $stmtMouvement->bindValue(':duree',    $duree,    PDO::PARAM_INT);
            $stmtMouvement->execute();

            $mouvements_json[] = [
                'angle' => (int)$angle,
                'duree' => (int)$duree
            ];
        }
    }
}

$son = $_POST['son'] ?? null;
$volume = isset($_POST['volume']) ? (int)$_POST['volume'] : 50;

// Extraire le numéro du titre si le fichier existe
$son_num = null;
if (!empty($son)) {
    if (preg_match('/Robot(\d+)\.mp3/i', $son, $matches)) {
        $son_num = (int)$matches[1]; // 1, 2, 3, ...
    } else {
        $son_num = 0; // ou null si pas trouvé
    }

    $stmtSon = $pdo->prepare("INSERT INTO sons (chore_id, son, volume) VALUES (:chore_id, :son, :volume)");
    $stmtSon->bindValue(':chore_id', $chore_id, PDO::PARAM_INT);
    $stmtSon->bindValue(':son', $son, PDO::PARAM_STR); // pour la DB on garde le nom original
    $stmtSon->bindValue(':volume', $volume, PDO::PARAM_INT);
    $stmtSon->execute();
}

// Construire le JSON
$data = [
    'chore_id'   => (int)$chore_id,
    'messages'   => $messages_json,
    'mouvements' => $mouvements_json,
    'son'        => $son_num,  // ici on envoie le numéro du titre
    'volume'     => $volume
];

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Chemin correct vers le dossier json existant
$path = __DIR__ . '/../../json/';
file_put_contents($path . "choregraphie_$chore_id.json", $json);

header("Location: ../index.php");
exit;

