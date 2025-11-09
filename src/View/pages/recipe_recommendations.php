<?php
// src/View/pages/recipe_recommendations.php

$title ??= 'Recept ajánlások';
?>

<div class="container py-4">
    <header class="mb-5">
        <h1 class="display-5 fw-bold">Recept ajánlások</h1>
        <p class="lead">
            Az alábbi recepteket ajánljuk a hűtőd tartalma alapján. Ezekhez a receptekhez 
            maximum 2 hozzávaló hiányzik a hűtődből.
        </p>
    </header>

    <?php if (empty($recipes)) : ?>
        <div class="alert alert-info shadow-sm">
            <h4 class="alert-heading">Nincs megfelelő recept ajánlatunk</h4>
            <p>Sajnos a jelenlegi hűtő tartalmad alapján nem találtunk olyan receptet, 
            amihez maximum 2 hozzávaló hiányzik. Próbálj feltölteni több alapanyagot a hűtődbe!</p>
            <hr>
            <p class="mb-0">
                <a href="/fridge" class="btn btn-primary">Hűtőszekrény kezelése</a>
                <a href="/recipes" class="btn btn-outline-secondary ms-2">Összes recept böngészése</a>
                <a href="/recipes/recommend/ai" class="btn btn-success ms-2">Recept ajánlás kérése</a>
            </p>
        </div>
    <?php else : ?>
        <?php if (count($recipes) <= 1) : ?>
            <div class="d-flex justify-content-end mb-4">
                <a href="/recipes/recommend/ai" class="btn btn-success">Recept ajánlás kérése</a>
            </div>
        <?php endif; ?>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($recipes as $recipe) : ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($recipe['image'] ?? '') ?>" class="card-img-top"
                                 alt="<?= htmlspecialchars($recipe['name'] ?? 'Recept') ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-2">
                                <?php
                                $missing = $missingCounts[$recipe['id']] ?? 0;
                                $badgeClass = $missing === 0 ? 'bg-success' : ($missing === 1 ?
                                    'bg-info' : 'bg-warning');
                                $badgeText = $missing === 0 ? 'Minden hozzávaló megvan!' : ($missing === 1 ?
                                    '1 hozzávaló hiányzik' : '2 hozzávaló hiányzik');
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($recipe['name']
                                    ?? 'Ismeretlen recept') ?></h5>
                            <p class="card-text text-muted mb-1 small">
                                <?= htmlspecialchars($recipe['category'] ?? 'Kategória nélkül') ?>
                            </p>
                            <p class="card-text flex-grow-1"><?= htmlspecialchars($recipe['short_description']
                                    ?? 'Nincs leírás') ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <a href="/recipe/<?= $recipe['id'] ?? 0 ?>"
                                   class="btn btn-outline-primary btn-sm">Részletek</a>
                                <small class="text-muted">
                                    <?= htmlspecialchars($recipe['created_by'] ?? 'ismeretlen') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
