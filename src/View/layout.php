<?php

use WebDevProject\Security\Csrf;

$csrf = \WebDevProject\Security\Csrf::token();
?>
<!-- src/View/layout.php -->
<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>" class="h-100">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'My Fridge') ?></title>
    <meta name="csrf-token" content="<?= \WebDevProject\Security\Csrf::token() ?>">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/global.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="stylesheet" href="/css/index.css">
    <link rel="stylesheet" href="/css/recipe.css">
    <link rel="stylesheet" href="/css/favorites.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
</head>
<body<?= isset($_SESSION['user_id']) ?
    ' class="user-logged-in d-flex flex-column h-100"' : ' class="d-flex flex-column h-100"' ?>>

<?php require __DIR__ . '/partials/nav.php'; ?>
<?php if (!empty($_SESSION['flash'])) : ?>
    <div class="container mt-3">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<main class="container py-3 flex-grow-1">
    <?php echo $content; ?>
</main>

<footer class="container-fluid py-3 mt-auto text-center">
    <div class="container">
        <p>FeRiWebDev - <?= date("Y"); ?></p>
    </div>
</footer>

<script src="/js/bootstrap.bundle.min.js"></script>
<script src="/js/theme-toggle.js"></script>
<script src="/js/functions.js"></script>
<script src="/js/form-validation.js"></script>
<script src="/js/modal-helpers.js"></script>
</body>
</html>
