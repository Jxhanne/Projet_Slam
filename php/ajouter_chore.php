<?php
include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
    body {
        background-color: #f5f5f5; /* gris clair */
    }

    .card {
        background-color: #ffffff;
        border-radius: 12px;
        border: 1px solid #ddd;
    }

    .movement {
        background: #fafafa;
        border: 1px solid #ddd !important;
        border-radius: 8px !important;
    }
</style>

<div class="container mt-5">

    <h1 class="text-center mb-4">Créer une Chorégraphie</h1>

    <div class="card shadow p-4">
        <form action="actions/creation_chore.php" method="POST">

            <!-- Message -->
            <div class="mb-4">
                <label class="form-label">Message à afficher :</label>
                <input type="text" class="form-control" name="message" required placeholder="Entrez un message...">
            </div>

            <h4>Mouvements du bras</h4>
            <p class="text-muted">Ajoutez autant de mouvements que vous voulez.</p>

            <div id="movement-container">
                <div class="movement p-3 mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label"> Angle (0 à 180°)</label>
                            <input type="number" class="form-control" name="angle[]" min="0" max="180" required placeholder="ex : 45">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Durée (ms)</label>
                            <input type="number" class="form-control" name="duration[]" min="100" required placeholder="ex : 1500">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mb-3" onclick="addMovement()">+ Ajouter un mouvement</button>

            <br>

            <button type="submit" class="btn btn-primary">Créer la chorégraphie</button>
            <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

<script>
    function addMovement() {
        const container = document.getElementById("movement-container");
        const newMovement = document.createElement("div");
        newMovement.classList.add("movement", "p-3", "mb-3");

        newMovement.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Angle (0 à 180°)</label>
                    <input type="number" class="form-control" name="angle[]" min="0" max="180" required placeholder="ex : 90">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Durée (ms)</label>
                    <input type="number" class="form-control" name="duration[]" min="100" required placeholder="ex : 2000">
                </div>
            </div>
        `;

        container.appendChild(newMovement);
    }
</script>

<?php
include 'footer.php';
?>
