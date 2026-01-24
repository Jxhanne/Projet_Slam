<?php
include "header.php";
include "config.php";

$pdo = new PDO(
        'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
        config::USER,
        config::PASSWORD
);

if (!isset($_GET['id'])) {
    echo "<p>Aucune chorégraphie indiquée.</p>";
    include "footer.php";
    exit;
}

$id = $_GET['id'];

$sql = "SELECT * FROM choregraphies WHERE id = :id";
$req = $pdo->prepare($sql);
$req->bindParam(':id', $id, PDO::PARAM_INT);
$req->execute();

$choreo = $req->fetch(PDO::FETCH_ASSOC);

if (!$choreo) {
    echo "<p>Chorégraphie introuvable.</p>";
    include "footer.php";
    exit;
}

$req = "SELECT * FROM messages WHERE chore_id = :id";
$req = $pdo->prepare($req);
$req->bindParam(':id', $id);
$req->execute();

$messages = $req->fetchAll(PDO::FETCH_ASSOC);

$sqlMov = "SELECT * FROM mouvements WHERE chore_id = :id";
$reqMov = $pdo->prepare($sqlMov);
$reqMov->bindParam(':id', $id, PDO::PARAM_INT);
$reqMov->execute();

$mouvements = $reqMov->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   SON (table sons)
================================ */
$son = null;

if (!empty($choreo['id'])) {

    $sqlSon = "SELECT * FROM sons WHERE chore_id = :id";
    $reqSon = $pdo->prepare($sqlSon);
    $reqSon->bindParam(':id', $id, PDO::PARAM_INT);
    $reqSon->execute();

    $son = $reqSon->fetch(PDO::FETCH_ASSOC);
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
    body {
        background-color: #4b4b4b;
    }

    h1 {
        color: #4b4b4b;
    }
</style>

<div class="container mt-5">
    <div class="card shadow p-4">

        <h1 class="text-center mb-4">Chorégraphie</h1>

        <p><strong>ID :</strong> <?= $choreo['id'] ?></p>
        <p><strong>Date :</strong> <?= $choreo['date_creation'] ?></p>

        <!-- ===============================
             MESSAGES
        ================================ -->
        <h3 class="mt-4">Messages</h3>

        <?php if (empty($messages)) : ?>
            <p>Aucun message.</p>
        <?php else : ?>
            <ul class="list-group mb-3">
                <?php foreach ($messages as $msg) : ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($msg['message']) ?><br>
                        <small>Durée : <?= $msg['duree'] ?> ms</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- ===============================
             MOUVEMENTS
        ================================ -->
        <h3 class="mt-4">Mouvements</h3>

        <?php if (empty($mouvements)) : ?>
            <p>Aucun mouvement.</p>
        <?php else : ?>
            <ul class="list-group mb-3">
                <?php foreach ($mouvements as $m) : ?>
                    <li class="list-group-item">
                        Angle : <?= $m['angle'] ?>° —
                        Durée : <?= $m['duree'] ?> ms
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- ===============================
             SON
        ================================ -->
        <!-- ===============================
     SON
=============================== -->
        <h3 class="mt-4">Son</h3>

        <?php if ($son) : ?>

            <div class="col-md-6">

                <label class="form-label">Son associé à la chorégraphie</label>

                <!-- Nom du fichier -->
                <p>
                    <strong>Fichier :</strong>
                    <?= htmlspecialchars($son['son']) ?>
                </p>

                <!-- Lecteur audio (aperçu) -->
                <audio id="player" controls style="margin-top:10px; width:100%">
                    <source src="/Projet_SLAM/sons/<?= htmlspecialchars($son['son']) ?>">
                    Votre navigateur ne supporte pas l'audio.
                </audio>

                <p class="mt-2">
                    <strong>Volume :</strong> <?= (int)$son['volume'] ?>%
                </p>

            </div>

        <?php else : ?>
            <p style="color:red;">Aucun son associé à cette chorégraphie.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt-3">← Retour</a>

    </div>
</div>

<?php include "footer.php"; ?>
