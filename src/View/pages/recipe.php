<?php
// src/View/pages/recipe.php
?>

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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h1 class="card-title display-5 fw-bold mb-0">
                            <?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></h1>
                            
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <?php if ($recipe['is_favorite']) : ?>
                                <form action="/profile/favorites/remove" method="post">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-heart-fill"></i> Eltávolítás a kedvencekből
                                    </button>
                                </form>
                            <?php else : ?>
                                <form action="/profile/favorites/add" method="post">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-heart"></i> Hozzáadás a kedvencekhez
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
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
                    
                    <div class="d-flex flex-wrap mt-3 gap-3">
                        <?php if (!empty($recipe['prep_time'])) : ?>
                        <div class="recipe-time-badge">
                            <i class="bi bi-clock"></i> Előkészítés: 
                            <span class="fw-bold"><?= htmlspecialchars($recipe['prep_time']) ?> perc</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($recipe['cook_time'])) : ?>
                        <div class="recipe-time-badge">
                            <i class="bi bi-fire"></i> Főzés: 
                            <span class="fw-bold"><?= htmlspecialchars($recipe['cook_time']) ?> perc</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($recipe['servings'])) : ?>
                        <div class="recipe-time-badge">
                            <i class="bi bi-people"></i> Adagok: 
                            <span class="fw-bold"><?= htmlspecialchars($recipe['servings']) ?> fő</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0 fw-bold">Hozzávalók</h3>
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
                    <h3 class="card-title mb-0 fw-bold">Elkészítés</h3>
                </div>
                <div class="card-body">
                    <p class="card-text fs-5">
                        <?= nl2br(htmlspecialchars($recipe['instructions'], ENT_QUOTES)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
