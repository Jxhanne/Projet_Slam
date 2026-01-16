<?php
include "header.php";
include "config.php";

// Connexion PDO
$pdo = new PDO(
        'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8', config::USER, config::PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Récupération des chorégraphies
$req = $pdo->query("SELECT * FROM choregraphies ORDER BY date_creation DESC");
$choreos = $req->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">

    <div class="text-center mb-4">
        <h1 class="display-5">Bienvenue dans votre centre de chorégraphie</h1>
        <p class="lead">Créez et gérez vos chorégraphies pour le robot</p>

        <a href="ajouter_chore.php" class="btn btn-primary btn-lg">
            + Ajouter une chorégraphie
        </a>
    </div>

    <hr>

    <h2 class="mb-3">Vos chorégraphies</h2>

    <?php if (empty($choreos)): ?>
        <div class="alert alert-info">
            Aucune chorégraphie enregistrée pour le moment.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($choreos as $c): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            Créée le : <?= htmlspecialchars($c['date_creation']) ?>
                        </small>
                    </div>

                    <a href="voir_choregraphie.php?id=<?= $c['id']; ?>" class="btn btn-outline-secondary">
                         Voir détails
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php
include "footer.php";
?>
