<?php
include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
    body {
        background-color: #4b4b4b;
    }

    h1 {
        color: #ffffff;
    }
    .card {
        background-color: #ffffff;
        border-radius: 12px;
        border: 1px solid rgba(0, 73, 255, 0.66);
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

            <!-- MESSAGES -->
            <h4>Message à afficher</h4>
            <p class="text-muted">Ajoutez autant de messages que vous voulez.</p>

            <div id="message-container">
                <div class="movement p-3 mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message[]" rows="2" required placeholder="Écris ton message ici"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Durée (ms)</label>
                            <input type="number" class="form-control" name="duree_message[]" min="100" required placeholder="ex : 1500">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mb-3" onclick="addMovementmessage()">+ Ajouter un message</button>

            <!-- MOUVEMENTS -->
            <h4>Mouvements du bras</h4>
            <p class="text-muted">Ajoutez autant de mouvements que vous voulez.</p>

            <div id="angle-container">
                <div class="movement p-3 mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Angle (0 à 180°)</label>
                            <input type="number" class="form-control" name="angle[]" min="0" max="180" required placeholder="ex : 45">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Durée (ms)</label>
                            <input type="number" class="form-control" name="duree_angle[]" min="100" required placeholder="ex : 1500">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mb-3" onclick="addMovementangle()">+ Ajouter un mouvement</button>

            <br>
            <!-- SON -->
            <h4>Son de la notification</h4>
            <p class="text-muted">Choisir le son que vous voulez</p>

            <div class="movement p-3 mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Choisir un son</label>
                        <select class="form-select" name="son" required>
                            <option value="">-- Sélectionnez une piste --</option>
                            <?php
                            $dir = __DIR__ . '/../sons/'; // dossier où sont stockés les fichiers audio
                            if (is_dir($dir)) {
                                $files = scandir($dir);
                                foreach ($files as $file) {
                                    if ($file === '.' || $file === '..') continue;
                                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                                    if (in_array(strtolower($ext), ['mp3','wav','ogg'])) {
                                        echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>

                        <!-- Lecture du son sélectionné -->
                        <audio id="player" controls style="margin-top:10px;">
                            <source src="" type="audio/mpeg">
                            Votre navigateur ne supporte pas l'audio.
                        </audio>
                    </div>

                    <div class="col-md-6">
                        <label for="range4" class="form-label">Volume du son choisi :</label>
                        <input type="range" class="form-range" min="0" max="100" name="volume" id="range4">
                        <output for="range4" id="rangeValue" aria-hidden="true"></output>
                    </div>
                </div>
            </div>

            <script>
                // Affichage valeur volume
                const rangeInput = document.getElementById('range4');
                const rangeOutput = document.getElementById('rangeValue');
                rangeOutput.textContent = rangeInput.value;

                rangeInput.addEventListener('input', function() {
                    rangeOutput.textContent = this.value;
                });

                // Lecture son sélectionné
                const selectSon = document.querySelector('select[name="son"]');
                const player = document.getElementById('player');

                selectSon.addEventListener('change', function() {
                    if (this.value) {
                        player.src = '../sons/' + this.value;
                        player.load();
                    }
                });
            </script>

            <button type="submit" class="btn btn-primary">Créer la chorégraphie</button>
            <a href="index.php" class="btn btn-outline-secondary">Annuler</a>

        </form>
    </div>
</div>
<script>
    function addMovementmessage() {
        const container = document.getElementById("message-container");
        const div = document.createElement("div");
        div.classList.add("movement", "p-3", "mb-3");

        div.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message[]" rows="2" required placeholder="Écris ton message ici"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Durée (ms)</label>
                    <input type="number" class="form-control" name="duree_message[]" min="100" required placeholder="ex : 2000">
                </div>
            </div>
        `;

        container.appendChild(div);
    }

    function addMovementangle() {
        const container = document.getElementById("angle-container");
        const div = document.createElement("div");
        div.classList.add("movement", "p-3", "mb-3");

        div.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Angle (0 à 180°)</label>
                    <input type="number" class="form-control" name="angle[]" min="0" max="180" required placeholder="ex : 90">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Durée (ms)</label>
                    <input type="number" class="form-control" name="duree_angle[]" min="100" required placeholder="ex : 2000">
                </div>
            </div>
        `;

        container.appendChild(div);
    }
</script>

<?php
include 'footer.php';
?>
