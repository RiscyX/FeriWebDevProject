<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once "../src/View/partials/nav.php"; ?>

<div class="form container py-5">
    <?php echo $formHtml; ?>
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

<script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
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
