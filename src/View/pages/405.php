<?php
use WebDevProject\Security\Csrf;
$title = '405 - Metódus nem engedélyezett';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="text-center">
                <div class="error-image mb-4">
                    <img src="/assets/405.png" alt="Hozzáférés megtagadva" class="img-fluid w-100" style="max-width: 400px;">
                </div>
                
                <div class="error-message">
                    <h2 class="h4 error-text">Nincs jogod ehhez</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-image {
    opacity: 0.9;
}

.error-image img {
    height: auto;
    object-fit: contain;
}

.error-message h2 {
    font-weight: 500;
    margin-top: 2rem;
}

.error-text {
    color: var(--navbar-text);
}

@media (max-width: 768px) {
    .error-image img {
        max-width: 280px;
    }
}

@media (max-width: 576px) {
    .error-image img {
        max-width: 250px;
    }
}

@media (max-width: 400px) {
    .error-image img {
        max-width: 200px;
    }
}
</style>
