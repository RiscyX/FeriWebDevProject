<?php
// src/View/pages/recipes.php
?>

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
    
    <!-- Szűrési lehetőségek -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Szűrés
            </h5>
        </div>
        <div class="card-body">
            <form id="recipe-filter-form" class="row g-3" method="GET" action="/recipes">
                <!-- Elkészítési idő szerinti szűrés -->
                <div class="col-md-4">
                    <label for="prep_time" class="form-label">Max. előkészítési idő (perc)</label>
                    <select class="form-select" id="prep_time" name="prep_time">
                        <option value="">Bármennyi</option>
                        <option value="15" <?= isset($filters['prep_time']) && $filters['prep_time'] == 15 ? 'selected'
                            : '' ?>>Max. 15 perc</option>
                        <option value="30" <?= isset($filters['prep_time']) && $filters['prep_time'] == 30 ? 'selected'
                            : '' ?>>Max. 30 perc</option>
                        <option value="45" <?= isset($filters['prep_time']) && $filters['prep_time'] == 45 ? 'selected'
                            : '' ?>>Max. 45 perc</option>
                        <option value="60" <?= isset($filters['prep_time']) && $filters['prep_time'] == 60 ? 'selected'
                            : '' ?>>Max. 1 óra</option>
                    </select>
                </div>
                
                <!-- Főzési idő szerinti szűrés -->
                <div class="col-md-4">
                    <label for="cook_time" class="form-label">Max. főzési idő (perc)</label>
                    <select class="form-select" id="cook_time" name="cook_time">
                        <option value="">Bármennyi</option>
                        <option value="15" <?= isset($filters['cook_time']) && $filters['cook_time'] == 15 ? 'selected'
                            : '' ?>>Max. 15 perc</option>
                        <option value="30" <?= isset($filters['cook_time']) && $filters['cook_time'] == 30 ? 'selected'
                            : '' ?>>Max. 30 perc</option>
                        <option value="45" <?= isset($filters['cook_time']) && $filters['cook_time'] == 45 ? 'selected'
                            : '' ?>>Max. 45 perc</option>
                        <option value="60" <?= isset($filters['cook_time']) && $filters['cook_time'] == 60 ? 'selected'
                            : '' ?>>Max. 1 óra</option>
                        <option value="120" <?= isset($filters['cook_time']) && $filters['cook_time'] == 120 ?
                            'selected' : '' ?>>Max. 2 óra</option>
                    </select>
                </div>
                
                <!-- Kategória szerinti szűrés -->
                <div class="col-md-4">
                    <label for="category" class="form-label">Kategória</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Összes kategória</option>
                        <?php if (isset($categories) && is_array($categories)) : ?>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= htmlspecialchars($category['name']) ?>" 
                                    <?= isset($filters['category']) && $filters['category'] == $category['name']
                                        ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <!-- Fallback opciók, ha nem sikerült betölteni a kategóriákat -->
                            <option value="Főétel" <?= isset($filters['category']) &&
                            $filters['category'] == 'Főétel' ? 'selected' : '' ?>>Főétel</option>
                            <option value="Leves" <?= isset($filters['category']) &&
                            $filters['category'] == 'Leves' ? 'selected' : '' ?>>Leves</option>
                            <option value="Előétel" <?= isset($filters['category']) &&
                            $filters['category'] == 'Előétel' ? 'selected' : '' ?>>Előétel</option>
                            <option value="Desszert" <?= isset($filters['category']) &&
                            $filters['category'] == 'Desszert' ? 'selected' : '' ?>>Desszert</option>
                            <option value="Saláta" <?= isset($filters['category']) &&
                            $filters['category'] == 'Saláta' ? 'selected' : '' ?>>Saláta</option>
                            <option value="Reggeli" <?= isset($filters['category']) &&
                            $filters['category'] == 'Reggeli' ? 'selected' : '' ?>>Reggeli</option>
                            <option value="Egyéb" <?= isset($filters['category']) &&
                            $filters['category'] == 'Egyéb' ? 'selected' : '' ?>>Egyéb</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="/recipes" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Szűrés törlése
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Szűrés
                    </button>
                </div>
            </form>
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
                        <h5 class="card-title"><?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></h5>
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
                        
                        <?php if (!empty($recipe['prep_time']) || !empty($recipe['cook_time'])) : ?>
                        <div class="d-flex flex-wrap gap-2 mt-2 mb-2">
                            <?php if (!empty($recipe['prep_time'])) : ?>
                            <span class="badge bg-info text-dark time-badge">
                                <i class="bi bi-clock"></i> <?= htmlspecialchars($recipe['prep_time']) ?> perc
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($recipe['cook_time'])) : ?>
                            <span class="badge bg-warning text-dark time-badge">
                                <i class="bi bi-fire"></i> <?= htmlspecialchars($recipe['cook_time']) ?> perc
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex gap-2 mb-2">
                            <a href="/recipe/<?= $recipe['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="bi bi-eye"></i> Megtekintés
                            </a>
                            
                            <?php if (isset($_SESSION['user_id'])) : ?>
                                <?php if (isset($recipe['is_favorite']) && $recipe['is_favorite']) : ?>
                                    <form action="/profile/favorites/remove" method="post" class="flex-grow-1">
                                        <input type="hidden" name="csrf"
                                               value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-heart-fill"></i> Eltávolítás
                                        </button>
                                    </form>
                                <?php else : ?>
                                    <form action="/profile/favorites/add" method="post" class="flex-grow-1">
                                        <input type="hidden" name="csrf"
                                               value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-heart"></i> Kedvencekhez
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <button type="button" class="btn btn-sm btn-success w-100" 
                                    data-bs-toggle="modal" data-bs-target="#menuModal<?= $recipe['id'] ?>">
                                <i class="bi bi-calendar-plus"></i> Hozzáadás a menühöz
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Lapozás -->
    <?php if (isset($totalPages) && $totalPages > 1) : ?>
    <div class="d-flex justify-content-center mt-5">
        <nav aria-label="Receptek lapozása">
            <ul class="pagination">
                <?php if ($page > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($filters['prep_time'])
                        ? '&prep_time=' . $filters['prep_time'] : '' ?><?= !empty($filters['cook_time']) ?
                        '&cook_time=' . $filters['cook_time'] : '' ?><?= !empty($filters['category']) ?
                        '&category=' . urlencode($filters['category']) : '' ?>" aria-label="Előző">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="visually-hidden">Előző</span>
                    </a>
                </li>
                <?php else : ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Előző">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="visually-hidden">Előző</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php
                // Korlátozzuk a lapozási linkek számát
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                // Ha az elejéről vagy végéről vannak oldalak, amelyek kiesnek a tartományból, akkor kompenzálunk
                if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' .
                        (!empty($filters['prep_time']) ? '&prep_time=' . $filters['prep_time'] : '') .
                        (!empty($filters['cook_time']) ? '&cook_time=' . $filters['cook_time'] : '') .
                        (!empty($filters['category']) ? '&category=' . urlencode($filters['category']) : '') .
                        '">1</a></li>';

                    if ($startPage > 2) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                        <a class="page-link" href="?page=' . $i .
                        (!empty($filters['prep_time']) ? '&prep_time=' . $filters['prep_time'] : '') .
                        (!empty($filters['cook_time']) ? '&cook_time=' . $filters['cook_time'] : '') .
                        (!empty($filters['category']) ? '&category=' . urlencode($filters['category']) : '') .
                        '">' . $i . '</a>
                    </li>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }

                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages .
                        (!empty($filters['prep_time']) ? '&prep_time=' . $filters['prep_time'] : '') .
                        (!empty($filters['cook_time']) ? '&cook_time=' . $filters['cook_time'] : '') .
                        (!empty($filters['category']) ? '&category=' . urlencode($filters['category']) : '') .
                        '">' . $totalPages . '</a></li>';
                }
                ?>
                
                <?php if ($page < $totalPages) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($filters['prep_time']) ?
                        '&prep_time=' . $filters['prep_time'] : '' ?><?= !empty($filters['cook_time']) ?
                        '&cook_time=' . $filters['cook_time'] : '' ?><?= !empty($filters['category']) ?
                        '&category=' . urlencode($filters['category']) : '' ?>" aria-label="Következő">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="visually-hidden">Következő</span>
                    </a>
                </li>
                <?php else : ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Következő">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="visually-hidden">Következő</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    
        <?php if ($totalRecipes > 0) : ?>
    <div class="text-center text-muted mt-3 mb-5">
        <small>Összesen <?= $totalRecipes ?> recept, <?= $totalPages ?> oldal</small>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modálok a dokumentum végén -->
<?php if (isset($_SESSION['user_id']) && !empty($recipes)) : ?>
    <?php foreach ($recipes as $recipe) : ?>
        <!-- Menü modal -->
        <div class="modal fade" id="menuModal<?= $recipe['id'] ?>" tabindex="-1" 
             aria-labelledby="menuModalLabel<?= $recipe['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="/menus/add" method="post">
                        <input type="hidden" name="csrf" value="<?= \WebDevProject\Security\Csrf::token() ?>">
                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                        
                        <div class="modal-header">
                            <h5 class="modal-title" id="menuModalLabel<?= $recipe['id'] ?>">
                                Hozzáadás a menühöz - <?= htmlspecialchars($recipe['title']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Bezárás"></button>
                        </div>
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="menuName<?= $recipe['id'] ?>" class="form-label">Menü neve</label>
                                <input type="text" class="form-control" id="menuName<?= $recipe['id'] ?>" 
                                       name="menu_name" required maxlength="10" 
                                       placeholder="Pl. Reggeli, Ebéd, Vacsora">
                                <div class="form-text">Add meg a menü nevét (max. 10 karakter)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dayOfWeek<?= $recipe['id'] ?>" class="form-label">Nap</label>
                                <select class="form-select" id="dayOfWeek<?= $recipe['id'] ?>" 
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
    <?php endforeach; ?>
<?php endif; ?>
