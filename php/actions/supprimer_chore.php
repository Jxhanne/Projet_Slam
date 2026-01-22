<?php
include_once "../config.php";

if (!isset($_GET['id'])) die("ID manquant");

$id = intval($_GET['id']);
if ($id <= 0) die("ID invalide !");

try {
    $pdo = new PDO(
        'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
        config::USER,
        config::PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Commencer une transaction
    $pdo->beginTransaction();

    // 1️⃣ Supprimer les messages liés
    $stmt = $pdo->prepare("DELETE FROM messages WHERE chore_id = ?");
    $stmt->execute([$id]);

    // 2️⃣ Supprimer les mouvements liés
    $stmt = $pdo->prepare("DELETE FROM mouvements WHERE chore_id = ?");
    $stmt->execute([$id]);

    // 3️⃣ Supprimer le son lié
    $stmt = $pdo->prepare("DELETE FROM sons WHERE chore_id = ?");
    $stmt->execute([$id]);

    // 4️⃣ Supprimer la chorégraphie
    $stmt = $pdo->prepare("DELETE FROM choregraphies WHERE id = ?");
    $stmt->execute([$id]);

    // Commit de la transaction
    $pdo->commit();

    // 5️⃣ Supprimer le fichier JSON
    $jsonFile = __DIR__ . '/../../json/choregraphie_' . $id . '.json';
    if (file_exists($jsonFile)) {
        unlink($jsonFile);
    }

    // Redirection
    header("Location: ../index.php?deleted=1");
    exit;

} catch (PDOException $e) {
    // Rollback en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erreur lors de la suppression : " . $e->getMessage());
}
