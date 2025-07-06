<?php

require __DIR__ . '/../partials/carousel.php';

?>

<!-- Kulcsfunkciók szekció -->
<section class="features py-5 mb-5" style="background: var(--secondary-bg);">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold" style="color: var(--primary-text);">
            Kulcsfunkciók
        </h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="feature-item d-flex mb-4">
                    <div class="feature-icon me-3">
                        <i class="bi bi-search fs-1" style="color: var(--navbar-text);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-text);">Villámgyors keresés</h5>
                        <p style="color: var(--desc-text);">Szűrés receptkategóriák és kulcsszavak szerint.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-item d-flex mb-4">
                    <div class="feature-icon me-3">
                        <i class="bi bi-refrigerator fs-1" style="color: var(--navbar-text);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-text);">"Hűtőszekrényem" mód</h5>
                        <p style="color: var(--desc-text);">Intelligens találatlista az általad begépelt hozzávalók alapján.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-item d-flex mb-4">
                    <div class="feature-icon me-3">
                        <i class="bi bi-heart-fill fs-1" style="color: var(--navbar-text);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-text);">Kedvencek kezelése</h5>
                        <p style="color: var(--desc-text);">Receptjeid elmentése, szerkesztése, törlése és offline böngészése bevásárlásnál.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-item d-flex mb-4">
                    <div class="feature-icon me-3">
                        <i class="bi bi-calendar-week fs-1" style="color: var(--navbar-text);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-text);">Heti menütervező</h5>
                        <p style="color: var(--desc-text);">Receptek gyors hozzárendelése a hét napjaihoz, egyetlen kattintással.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA szekció -->
<section class="cta-section py-5 text-center" style="background: var(--hero-bg);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-4" style="color: var(--primary-text);">
                    Csatlakozz most!
                </h2>
                <p class="lead mb-4" style="color: var(--desc-text);">
                    Regisztrálj ingyen, spórold meg a felesleges bevásárlást, és inspirálódj nap mint nap új ízekkel!
                </p>
                <p class="mb-4" style="color: var(--desc-text);">
                    Fedezd fel, mi sülhet ki abból, ami már most a hűtődben van.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/register" class="btn btn-lg px-4 py-3" style="background: var(--btn-bg); color: var(--btn-text); border: none;">
                            Regisztráció <i class="bi bi-person-plus ms-2"></i>
                        </a>
                        <a href="/login" class="btn btn-outline-secondary btn-lg px-4 py-3">
                            Bejelentkezés <i class="bi bi-box-arrow-in-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="/fridge" class="btn btn-lg px-4 py-3" style="background: var(--btn-bg); color: var(--btn-text); border: none;">
                            Hűtőszekrényem <i class="bi bi-refrigerator ms-2"></i>
                        </a>
                        <a href="/recipes" class="btn btn-outline-secondary btn-lg px-4 py-3">
                            Receptek böngészése <i class="bi bi-book ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
