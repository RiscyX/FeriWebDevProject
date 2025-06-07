<?php
// ez a fájl futó logikát tartalmaz, és markup-ként használjuk
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use WebDevProject\Helper\NavHelper;

$navItems = NavHelper::getNavItems();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">Hűtőszekrényem</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-expanded="false" aria-label="Navigáció váltása">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($navItems as $item) :
                    $isActive = basename($_SERVER['PHP_SELF']) === basename($item['href']);
                    ?>
                    <li class="nav-item">
                        <a
                                class="nav-link<?= $isActive ? ' active' : '' ?>"
                                aria-current="<?= $isActive ? 'page' : '' ?>"
                                href="<?= htmlspecialchars($item['href'], ENT_QUOTES) ?>"
                        >
                            <?= htmlspecialchars($item['label'], ENT_QUOTES) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
