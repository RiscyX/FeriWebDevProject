<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-mail megerősítése</title>
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require __DIR__ . '/../../partials/nav.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="alert alert-<?= htmlspecialchars($type) ?> d-flex flex-column gap-3" role="alert">
                <p class="mb-0"><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
                <div class="text-end">
                    <a href="/login.php" class="btn btn-primary">Bejelentkezés</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
