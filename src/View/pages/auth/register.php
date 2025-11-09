<?php $title ??= 'Regisztráció'; ?>

<?php if (!empty($_SESSION['flash'])) : ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash'], ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="container py-5">
    <section class="hero mb-4" style="padding:2.5rem 2rem 2rem 2rem;">
        <h1 class="hero-title">Regisztráció</h1>
        <p class="hero-desc">
            Regisztrálj, hogy a hűtőd tartalmát egy helyen áttekinthesd, és
            könnyedén megtervezd a következő bevásárlást.
        </p>
    </section>
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <?= $formHtml ?>
        </div>
    </div>
</div>
