<?php
// src/View/pages/recipes.php
?>
<link rel="stylesheet" href="/css/recipe.css">

<div class="container py-4">
    <div class="d-md-flex justify-content-between align-items-md-center mb-4 flex-column flex-md-row">
        <h1 class="mb-3 mb-md-0">Receptek</h1>
        <div class="d-flex gap-2 flex-column flex-sm-row">
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 1) : ?>
                <a href="/admin/recipes" class="btn btn-outline-primary mb-2 mb-sm-0">
                    <i class="bi bi-clipboard-check"></i> Adminisztráció - Beküldött receptek
                </a>
            <?php endif; ?>
            
            <a href="/recipe/submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Recept beküldése
            </a>
        </div>
    </div>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php foreach ($recipes as $recipe) : ?>
            <div class="col">
                <div class="card h-100 shadow-sm rounded recipe-card">
                    <img src="<?= htmlspecialchars($recipe['image'], ENT_QUOTES) ?>" 
                         class="card-img-top recipe-card-image" 
                         alt="<?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?>">
                    <div class="card-body">
                        <h5 class="recipe-card-title"><?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></h5>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <span class="badge rounded-pill bg-primary mb-1">
                                <?= htmlspecialchars($recipe['category'], ENT_QUOTES) ?></span>
                            <span class="text-muted small">
                                <i class="bi bi-person">
                                </i> <?= htmlspecialchars($recipe['created_by'], ENT_QUOTES) ?>
                            </span>
                        </div>
                        <p class="card-text small">
                            <?= htmlspecialchars(
                                mb_strlen($recipe['description']) > 100
                                    ? mb_substr($recipe['description'], 0, 100) . '...'
                                    : $recipe['description'],
                                ENT_QUOTES
                            ) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="/recipe/<?= $recipe['id'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Recept megtekintése
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Később ide jöhet a lapozás -->
</div>
