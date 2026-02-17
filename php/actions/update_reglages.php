<?php
include "../config.php";

$pdo = new PDO(
    'mysql:host=' . config::HOST . ';dbname=' . config::DBNAME . ';charset=utf8',
    config::USER,
    config::PASSWORD
);

$sql = "UPDATE reglages SET
        debut_semaine = :debut_semaine,
        fin_semaine = :fin_semaine,
        debut_weekend = :debut_weekend,
        fin_weekend = :fin_weekend
        WHERE id = 1";

$req = $pdo->prepare($sql);

$req->bindParam(':debut_semaine', $_POST['debut_semaine']);
$req->bindParam(':fin_semaine', $_POST['fin_semaine']);
$req->bindParam(':debut_weekend', $_POST['debut_weekend']);
$req->bindParam(':fin_weekend', $_POST['fin_weekend']);

$req->execute();

header("Location: ../reglages.php");
exit;
