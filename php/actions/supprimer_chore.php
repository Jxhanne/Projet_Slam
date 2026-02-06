<?php
include_once "../config.php";

if (!isset($_GET['id'])) die("ID manquant");

$id = intval($_GET['id']);
if ($id <= 0) die("ID invalide !");


    $pdo = new PDO(
        'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
        config::USER,
        config::PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Supprimer les messages liés
    $stmt = $pdo->prepare("DELETE FROM messages WHERE chore_id = ?");
    $stmt->execute([$id]);

    // Supprimer les mouvements liés
    $stmt = $pdo->prepare("DELETE FROM mouvements WHERE chore_id = ?");
    $stmt->execute([$id]);

    // Supprimer le son lié
    $stmt = $pdo->prepare("DELETE FROM sons WHERE chore_id = ?");
    $stmt->execute([$id]);

    // Supprimer la chorégraphie
    $stmt = $pdo->prepare("DELETE FROM choregraphies WHERE id = ?");
    $stmt->execute([$id]);

    // Supprimer le fichier JSON
    $jsonFile = __DIR__ . '/../../json/choregraphie_' . $id . '.json';
    if (file_exists($jsonFile)) {
        unlink($jsonFile);
    }

    // Renvoie sur index
    header("Location: ../index.php?deleted=1");
    exit;

