<!-- src/View/layout.php -->
<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Hűtőszekrényem') ?></title>

    <!-- ABSZOLÚT útvonalak, hogy /login-nál se törjenek -->
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/global.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="./favicon.png" type="image/png">
    <link rel="stylesheet" href="./css/index.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/partials/nav.php'; ?>

<main class="container py-3">
    <?= $content /* <- aktuális oldal HTML-je */ ?>
</main>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
