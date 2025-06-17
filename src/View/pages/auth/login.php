<?php
<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link href="./css/global.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/index.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
</head>
<body>

// -------------------------------------------------
// 1) Oldalcím — a layout ezt használja a <title>-hez
// -------------------------------------------------
$title = 'Bejelentkezés';

// -------------------------------------------------
// 2) Tartalom — innen lefelé kizárólag a fő rész
// -------------------------------------------------
?>
<div class="container py-5">
    <section class="hero mb-4" style="padding:2.5rem 2rem 2rem 2rem;">
        <h1 class="hero-title">Bejelentkezés</h1>
        <p class="hero-desc">
            Lépj be, hogy elérd a hűtőd tartalmát, és kezelhesd a termékeidet egyszerűen, átláthatóan!
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

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
