<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <title>Hűtőszekrényem</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/global.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.png" type="image/png">
</head>
<body>

 <?php require_once "../src/View/partials/nav.php"; ?>

<main class="container py-3">
    <?php require_once '../src/View/partials/carousel.php'; ?>

</main>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
<?php
