<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-mail megerősítése</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="icon" href="favicon.png" type="image/png">
</head>
<body>

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

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
