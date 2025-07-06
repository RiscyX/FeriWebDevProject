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
                    <h1 class="card-title display-5 fw-bold mb-3 text-center text-md-start">
                        <?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?>
                    </h1>
                            
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <div class="d-flex flex-column mb-3 gap-2">
                            <?php if ($recipe['is_favorite']) : ?>
                                <form action="/profile/favorites/remove" method="post">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-heart-fill"></i> Eltávolítás a kedvencekből
                                    </button>
                                </form>
                            <?php else : ?>
                                <form action="/profile/favorites/add" method="post">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-heart"></i> Hozzáadás a kedvencekhez
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <button type="button" class="btn btn-success w-100" 
                                    data-bs-toggle="modal" data-bs-target="#menuModal">
                                <i class="bi bi-calendar-plus"></i> Hozzáadás a menühöz
                            </button>
                        </div>
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

<!-- Menü modal a dokumentum végén -->
<?php if (isset($_SESSION['user_id'])) : ?>
<div class="modal fade" id="menuModal" tabindex="-1" 
     aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/menus/add" method="post">
                <input type="hidden" name="csrf" value="<?= \WebDevProject\Security\Csrf::token() ?>">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalLabel">
                        Hozzáadás a menühöz - <?= htmlspecialchars($recipe['name']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="menuName" class="form-label">Menü neve</label>
                        <input type="text" class="form-control" id="menuName" 
                               name="menu_name" required maxlength="10" 
                               placeholder="Pl. Reggeli, Ebéd, Vacsora">
                        <div class="form-text">Add meg a menü nevét (max. 10 karakter)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dayOfWeek" class="form-label">Nap</label>
                        <select class="form-select" id="dayOfWeek" 
                                name="day_of_week" required>
                            <option value="Monday">Hétfő</option>
                            <option value="Tuesday">Kedd</option>
                            <option value="Wednesday">Szerda</option>
                            <option value="Thursday">Csütörtök</option>
                            <option value="Friday">Péntek</option>
                            <option value="Saturday">Szombat</option>
                            <option value="Sunday">Vasárnap</option>
                        </select>
                        <div class="form-text">Válaszd ki, melyik napra szeretnéd hozzáadni</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégsem</button>
                    <button type="submit" class="btn btn-success">Hozzáadás a menühöz</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
