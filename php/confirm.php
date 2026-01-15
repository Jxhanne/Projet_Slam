<?php
include "header.php";
include "config.php";

// Connexion PDO
$pdo = new PDO(
        'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
        config::USER,
        config::PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Récupération de l'ID passé en paramètre
$id = $_GET['id'] ?? '';

if ($id === '') {
    echo "<p>Aucune chorégraphie indiquée.</p>";
    include "footer.php";
    exit;
}

// Récupérer la chorégraphie
$req = $pdo->prepare("SELECT * FROM choregraphies WHERE id = ?");
$req->execute([$id]);
$choreo = $req->fetch(PDO::FETCH_ASSOC);

if (!$choreo) {
    echo "<p>Chorégraphie introuvable.</p>";
    include "footer.php";
    exit;
}

// Récupérer les mouvements
$req2 = $pdo->prepare("SELECT * FROM mouvements WHERE chore_id = ?");
$req2->execute([$id]);
$mouvements = $req2->fetchAll(PDO::FETCH_ASSOC);

// Chemin du JSON
$jsonFile = "../json/" . $id . ".json";
?>

<!-- BOOTSTRAP -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<div class="container mt-5">
    <div class="card shadow p-4">
        <h1 class="mb-3"> Chorégraphie créée !</h1>

        <p><strong>ID :</strong> <?= htmlspecialchars($choreo['id']) ?></p>
        <p><strong>Message :</strong> <?= htmlspecialchars($choreo['message']) ?></p>
        <p><strong>Date :</strong> <?= htmlspecialchars($choreo['date_creation']) ?></p>

        <h3 class="mt-4">Mouvements :</h3>

        <?php if (empty($mouvements)): ?>
            <div class="alert alert-warning">Aucun mouvement enregistré.</div>
        <?php else: ?>
            <ul class="list-group mb-3">
                <?php foreach ($mouvements as $m): ?>
                    <li class="list-group-item">
                        Angle : <?= (int)$m['angle'] ?>° —
                        Durée : <?= (int)$m['duration'] ?> ms
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h4>Fichier JSON :</h4>
        <?php if (file_exists($jsonFile)): ?>
            <a href="<?= $jsonFile ?>" class="btn btn-outline-primary mb-3" target="_blank">
                Voir le JSON
            </a>
        <?php else: ?>
            <div class="alert alert-danger"> Aucun fichier JSON trouvé.</div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="btn btn-secondary"><-- Retour à l'accueil</a>
    </div>
</div>

<?php
include "footer.php";
?>
