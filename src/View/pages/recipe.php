<?php
// src/View/pages/recipe_view.php
?>
<link rel="stylesheet" href="/css/recipe.css">

<div class="container py-4 recipe-view">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/recipes">Receptek</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></li>
        </ol>
    </nav>
    
    <div class="card mb-4 shadow-sm">
        <div class="row g-0">
            <div class="col-md-4 recipe-image-container">
                <img src="<?= htmlspecialchars($recipe['image'], ENT_QUOTES) ?>" 
                     class="img-fluid rounded-start recipe-detail-image" 
                     alt="<?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?>">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h1 class="recipe-card-title display-5 fw-bold">
                        <?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></h1>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <span class="badge rounded-pill bg-primary fs-6 py-2 px-3 mb-2 mb-md-0">
                            <?= htmlspecialchars($recipe['category'], ENT_QUOTES) ?></span>
                        <div class="d-flex flex-wrap">
                            <span class="text-muted me-3 fs-6">
                                <i class="bi bi-person">

                                </i> <?= htmlspecialchars($recipe['created_by'], ENT_QUOTES) ?>
                            </span>
                            <span class="text-muted fs-6">
                                <i class="bi bi-calendar"></i>
                                <?= htmlspecialchars($recipe['created_at'], ENT_QUOTES) ?>
                            </span>
                        </div>
                    </div>
                    <p class="card-text fs-5"><?= htmlspecialchars($recipe['description'], ENT_QUOTES) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="recipe-card-title mb-0 fw-bold">Hozzávalók</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recipe['ingredients'] as $ingredient) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center fs-5">
                                <?= htmlspecialchars($ingredient['name'], ENT_QUOTES) ?>
                                <span class="badge bg-secondary rounded-pill fs-6">
                                    <?= htmlspecialchars($ingredient['quantity'], ENT_QUOTES) ?> 
                                    <?= htmlspecialchars($ingredient['unit'], ENT_QUOTES) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="recipe-card-title mb-0 fw-bold">Elkészítés</h3>
                </div>
                <div class="card-body">
                    <p class="card-text fs-5">
                        <?= nl2br(htmlspecialchars($recipe['instructions'], ENT_QUOTES)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
