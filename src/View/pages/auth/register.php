<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/index.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/png">
</head>
<body>

<?php require_once "../src/View/partials/nav.php"; ?>

<div class="container py-5">
    <section class="hero mb-4" style="padding:2.5rem 2rem 2rem 2rem;">
        <h1 class="hero-title">Regisztráció</h1>
        <p class="hero-desc">
            Regisztrálj, hogy a hűtőd tartalmát egy helyen áttekinthesd,
            és könnyedén megtervezd a következő bevásárlást.
        </p>
    </section>
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card p-4">
                <?= $formHtml ?>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap modal markup -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Sikeres regisztráció</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
            </div>
            <div class="modal-body">
                <?= htmlspecialchars($_SESSION['success'] ?? '', ENT_QUOTES) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
<?php if (isset($_SESSION['success'])) :
    unset($_SESSION['success']);
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        });
    </script>
<?php endif; ?>
</body>
</html>
