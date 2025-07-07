<?php

$title = 'AI Recept Ajánlás';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">AI Recept Ajánlás</h1>
                <a href="/recipes/recommend" class="btn btn-outline-secondary">Vissza az ajánlásokhoz</a>
            </div>
            
            <div class="alert alert-info">
                <p class="mb-0">Ez a recept ajánlás az általad megadott hozzávalók
                    alapján készült, a Google Gemini AI segítségével.</p>
                <p class="mb-2"><strong>Alapanyagaid:</strong>
                    <?= htmlspecialchars(implode(', ', $ingredients ?? [])) ?></p>
            </div>
        </div>
    </div>

    <?php if (empty($aiRecommendation)) : ?>
        <div class="alert alert-warning">
            <h4>Sajnos nem sikerült receptet generálni</h4>
            <p>Próbálj meg több alapanyagot megadni a hűtődben, vagy próbáld újra később.</p>
            <a href="/fridge" class="btn btn-primary">Hűtő kezelése</a>
        </div>
    <?php else : ?>
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0"><?= htmlspecialchars($aiRecommendation['title'] ?? 'Ajánlott recept') ?></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <h5>Leírás</h5>
                        <p><?= htmlspecialchars($aiRecommendation['description'] ?? '') ?></p>
                        
                        <div class="time-badges-container">
                            <span class="badge bg-info text-dark time-badge">
                                <i class="bi bi-clock"></i> 
                                Előkészítés: <?= htmlspecialchars($aiRecommendation['preparationTime']
                                    ?? 'ismeretlen') ?> perc
                            </span>
                            <?php if (!empty($aiRecommendation['cookTime'])) : ?>
                            <span class="badge bg-info text-dark time-badge">
                                <i class="bi bi-fire"></i> 
                                Főzés: <?= htmlspecialchars($aiRecommendation['cookTime']
                                    ?? 'ismeretlen') ?> perc
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <h5>Hozzávalók<?php if (!empty($aiRecommendation['servings'])) :
                            ?> <small class="text-muted">(<?= htmlspecialchars($aiRecommendation['servings']) ?>
                                adag)</small><?php
                                      endif; ?></h5>
                        <ul class="list-group list-group-flush">
                            <?php if (
                            !empty($aiRecommendation['ingredients']) &&
                                is_array($aiRecommendation['ingredients'])
) : ?>
                                          <?php foreach ($aiRecommendation['ingredients'] as $ingredient) : ?>
                                                <?php if (is_array($ingredient) && isset($ingredient['name'])) : ?>
                                        <li class="list-group-item">
                                                    <?= htmlspecialchars($ingredient['quantity'] ?? '') ?> 
                                                    <?= htmlspecialchars($ingredient['unit'] ?? '') ?> 
                                                    <?= htmlspecialchars($ingredient['name']) ?>
                                        </li>
                                                <?php else : ?>
                                        <li class="list-group-item"><?= htmlspecialchars($ingredient) ?></li>
                                                <?php endif; ?>
                                          <?php endforeach; ?>
                            <?php else : ?>
                                <li class="list-group-item">Nincs megadva hozzávaló</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="col-md-8">
                        <h5>Elkészítés</h5>
                        <?php
                        $instructions = $aiRecommendation['instructions'] ?? '';
                        if (!empty($instructions) && !is_array($instructions)) {
                            // If there are numbers or dots in the text, it's likely divided into steps
                            $steps = preg_split('/\r?\n|\r/', $instructions);

                            echo '<ol class="instructions-list">';
                            foreach ($steps as $step) {
                                $step = trim($step);
                                if (!empty($step)) {
                                    $step = preg_replace('/^(\d+[\.\)]|\-)\s*/', '', $step);
                                    echo '<li>' . htmlspecialchars($step) . '</li>';
                                }
                            }
                            echo '</ol>';
                        } elseif (is_array($instructions)) {
                            echo '<ol class="instructions-list">';
                            foreach ($instructions as $step) {
                                echo '<li>' . htmlspecialchars($step) . '</li>';
                            }
                            echo '</ol>';
                        } else {
                            echo '<p>' . htmlspecialchars($instructions) . '</p>';
                        }
                        ?>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="alert alert-light border">
                            <p class="small text-muted mb-0">
                                <i class="bi bi-info-circle"></i> Ez egy AI által generált recept ajánlás. 
                                Az elkészítés előtt ellenőrizd a hozzávalókat és a lépéseket.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="/recipes/recommend" class="btn btn-outline-primary">Vissza az ajánlásokhoz</a>
                    <div>
                        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                                data-bs-target="#saveRecipeModal">
                            <i class="bi bi-save"></i> Recept mentése
                        </button>
                        <a href="/recipes/recommend/ai" class="btn btn-primary">Új AI ajánlás kérése</a>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="saveRecipeModal" tabindex="-1" aria-labelledby="saveRecipeModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/recipes/save-ai-recipe" method="POST">
                            <?= \WebDevProject\Security\Csrf::field() ?>
                            <input type="hidden" name="recipe_data"
                                   value="<?= htmlspecialchars(json_encode($aiRecommendation)) ?>">
                            
                            <div class="modal-header">
                                <h5 class="modal-title" id="saveRecipeModalLabel">Recept mentése</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="recipe_title" class="form-label">Recept neve</label>
                                    <input type="text" class="form-control" id="recipe_title" name="recipe_title" 
                                           value="<?= htmlspecialchars($aiRecommendation['title'] ?? '') ?>"
                                           required>
                                    <div class="form-text">Módosíthatod a recept nevét, ha szeretnéd.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="recipe_category" class="form-label">Kategória</label>
                                    <select class="form-select" id="recipe_category" name="recipe_category">
                                        <option value="Főétel">Főétel</option>
                                        <option value="Leves">Leves</option>
                                        <option value="Előétel">Előétel</option>
                                        <option value="Desszert">Desszert</option>
                                        <option value="Saláta">Saláta</option>
                                        <option value="Reggeli">Reggeli</option>
                                        <option value="Egyéb">Egyéb</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégsem</button>
                                <button type="submit" class="btn btn-success">Mentés</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.instructions-list li {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}
</style>
