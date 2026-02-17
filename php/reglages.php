<?php
include "header.php";
include "config.php";

$pdo = new PDO(
    'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
    config::USER,
    config::PASSWORD
);

$sql = "SELECT * FROM reglages LIMIT 1";
$req = $pdo->prepare($sql);
$req->execute();
$reglage = $req->fetch(PDO::FETCH_ASSOC);
?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <div class="container mt-5">
        <div class="card shadow p-4">

            <h1 class="mb-4">Réglages Bisik</h1>

            <form action="actions/update_reglages.php" method="POST">

                <h4>Jours de semaine</h4>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Début</label>
                        <input type="time" name="debut_semaine" class="form-control"
                               value="<?= $reglage['debut_semaine'] ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Fin</label>
                        <input type="time" name="fin_semaine" class="form-control"
                               value="<?= $reglage['fin_semaine'] ?>" required>
                    </div>
                </div>

                <h4>Weekend</h4>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Début</label>
                        <input type="time" name="debut_weekend" class="form-control"
                               value="<?= $reglage['debut_weekend'] ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Fin</label>
                        <input type="time" name="fin_weekend" class="form-control"
                               value="<?= $reglage['fin_weekend'] ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Enregistrer
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Retour
                </a>

            </form>
        </div>
    </div>

<?php include "footer.php"; ?>
