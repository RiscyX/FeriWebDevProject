<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

var_dump($_SESSION);
?>
<!-- src/View/layout.php -->
<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Hűtőszekrényem') ?></title>

    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/global.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="./favicon.png" type="image/png">
    <link rel="stylesheet" href="./css/index.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/partials/nav.php'; ?>
<?php if (!empty($_SESSION['flash'])) : ?>
    <div class="container mt-3">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<main class="container py-3">
    <?= $content ?>
</main>

<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
