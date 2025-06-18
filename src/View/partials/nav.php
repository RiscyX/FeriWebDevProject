<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
use WebDevProject\Helper\NavHelper;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navItems = NavHelper::getNavItems();
?>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand fs-3 px-2" href="/">Hűtőszekrényem</a>

        <!-- hamburger gomb minden eszközön, de collapse-olja a #mainNav-ot -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Navigáció váltása">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- EZ a blokk töltődik be desktopon és mobilon egyaránt -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php foreach ($navItems as $item) : ?>
                    <?php if (!empty($item['type']) && $item['type'] === 'button') : ?>
                        <li class="nav-item d-flex align-items-center">
                            <button
                                    id="<?= htmlspecialchars($item['id'], ENT_QUOTES) ?>"
                                    class="fs-5 px-6 <?= htmlspecialchars($item['class'], ENT_QUOTES) ?>"
                                <?= !empty($item['aria-label'])
                                    ? 'aria-label="' . htmlspecialchars($item['aria-label'], ENT_QUOTES) . '"'
                                    : '' ?>
                            >
                                <?= $item['label'] ?>
                            </button>
                        </li>
                    <?php else :
                        $isActive = basename($_SERVER['PHP_SELF']) === basename($item['href']);
                        ?>
                        <li class="nav-item">
                            <a class="nav-link fs-5 px-6<?= $isActive ? ' active' : '' ?>"
                                <?= $isActive ? 'aria-current="page"' : '' ?>
                               href="<?= htmlspecialchars($item['href'], ENT_QUOTES) ?>">
                                <?= htmlspecialchars($item['label'], ENT_QUOTES) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <!-- Theme toggle button -->
                <li class="nav-item d-flex align-items-center">
                    <button id="themeToggle"
                            class="btn btn-outline-secondary fs-5 px-3 ms-2"
                            aria-label="Toggle theme">
                        🌓
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
