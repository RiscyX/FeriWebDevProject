<?php
// src/View/pages/menus.php
?>

<div class="container py-4">
    <div class="d-md-flex d-block justify-content-between align-items-center mb-4">
        <h1 class="mb-3 mb-md-0">Heti menü</h1>
        <a href="/recipes" class="btn btn-primary w-100 w-md-auto">
            <i class="bi bi-search"></i> Receptek böngészése
        </a>
    </div>
    
    <?php if (empty($menus)) : ?>
    <div class="alert alert-info">
        <h4 class="alert-heading">Még nincs egyetlen menü sem!</h4>
        <p>Készíts hetitervet az ételekből, hogy könnyebben tudj vásárolni és főzni.</p>
        <hr>
        <p class="mb-0">
            Böngéssz a <a href="/recipes" class="alert-link">receptek</a> között, és az "Hozzáadás a menühöz"
            gombra kattintva adj hozzá recepteket a heti menüdhöz.
        </p>
    </div>
    <?php else : ?>
    <div class="row mb-4">
        <?php
        $menusByDay = [];
        foreach ($menus as $menu) {
            if (!isset($menusByDay[$menu['day_of_week']])) {
                $menusByDay[$menu['day_of_week']] = [];
            }
            $menusByDay[$menu['day_of_week']][] = $menu;
        }

        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($dayOrder as $day) :
            $dayName = $dayNames[$day] ?? $day;
            ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="mb-0 fs-4"><?= htmlspecialchars($dayName) ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($menusByDay[$day]) || empty($menusByDay[$day])) : ?>
                            <div class="text-muted text-center py-4 menu-day-empty">
                                <i class="bi bi-calendar-x" style="font-size: 2.5rem;"></i>
                                <div class="mt-2 fs-5">Nincs hozzáadott étel</div>
                            </div>
                        <?php else : ?>
                            <?php foreach ($menusByDay[$day] as $menu) : ?>
                                <div class="menu-item mb-3">
                                    <div class="d-flex flex-row menu-item-container">
                                        <div class="flex-shrink-0 menu-img-container">
                                            <img src="<?= htmlspecialchars($menu['image']) ?>"
                                                 alt="<?= htmlspecialchars($menu['recipe_name']) ?>"
                                                 class="menu-item-image rounded">
                                        </div>
                                        <div class="flex-grow-1 ms-3 menu-content-container">
                                            <h6 class="menu-item-title">
                                                <a href="/recipe/<?= $menu['recipe_id'] ?>">
                                                    <?= htmlspecialchars($menu['recipe_name']) ?></a>
                                            </h6>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                <span class="badge bg-secondary menu-badge">
                                                    <?= htmlspecialchars($menu['name']) ?></span>
                                                <form action="/menus/remove" method="post"
                                                      class="ms-auto menu-form-container">
                                                    <input type="hidden" name="csrf"
                                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                                    <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger menu-delete-btn"
                                                            onclick="return confirm('Biztosan törlöd ezt' +
                                                             ' az ételt a menüből?')">
                                                        Törlés
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($day === 'Wednesday') : ?>
                </div><div class="row mb-4">
            <?php endif; ?>
            
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="/css/menus.css">
