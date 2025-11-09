<?php

// src/View/pages/recipe_submit.php
use WebDevProject\Security\Csrf;

?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/recipes">Receptek</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recept beküldése</li>
        </ol>
    </nav>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="/recipe/submit" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
                
                <div class="mb-3">
                    <label for="recipeName" class="form-label">Recept neve *</label>
                    <input type="text" class="form-control" id="recipeName" name="name" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="recipeCategory" class="form-label">Kategória *</label>
                        <select class="form-select" id="recipeCategory" name="category" required>
                            <option value="">-- Válassz kategóriát --</option>
                            <?php if (isset($categories) && !empty($categories)) : ?>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($category['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="Előétel">Előétel</option>
                                <option value="Leves">Leves</option>
                                <option value="Főétel">Főétel</option>
                                <option value="Desszert">Desszert</option>
                                <option value="Saláta">Saláta</option>
                                <option value="Reggeli">Reggeli</option>
                                <option value="Egyéb">Egyéb</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="recipeImage" class="form-label">Kép feltöltése</label>
                        <input class="form-control" type="file" id="recipeImage" name="image" accept="image/*">
                        <div class="form-text">Ajánlott: legalább 800x600 pixel felbontású kép</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="prepTime" class="form-label">Előkészítési idő (perc)</label>
                        <input type="number" class="form-control" id="prepTime" name="prep_time" min="1"
                               placeholder="Pl. 15">
                    </div>
                    <div class="col-md-4">
                        <label for="cookTime" class="form-label">Elkészítési idő (perc)</label>
                        <input type="number" class="form-control" id="cookTime" name="cook_time" min="1"
                               placeholder="Pl. 30">
                    </div>
                    <div class="col-md-4">
                        <label for="servings" class="form-label">Adagok száma</label>
                        <input type="number" class="form-control" id="servings" name="servings" min="1"
                               placeholder="Pl. 4">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="recipeDescription" class="form-label">Rövid leírás *</label>
                    <textarea class="form-control" id="recipeDescription" name="description" rows="2"
                              required></textarea>
                    <div class="form-text">Maximum 200 karakter rövid leírás a receptről</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Hozzávalók *</label>
                    <div id="ingredientsContainer">
                        <div class="row mb-2 ingredient-row">
                            <label class="form-label ingredient-label">Hozzávaló</label>
                            <div class="col-5">
                                <input type="text" class="form-control ingredient-name" 
                                       list="ingredientList0" 
                                       name="ingredients[0][name]" 
                                       data-index="0"
                                       placeholder="Hozzávaló neve" 
                                       autocomplete="off"
                                       required>
                                <datalist id="ingredientList0"></datalist>
                                <input type="hidden" name="ingredients[0][ingredient_id]" class="ingredient-id"
                                       id="ingredientId0" value="">
                            </div>
                            <div class="col-4">
                                <label class="form-label ingredient-label">Mennyiség</label>
                                <div class="input-group">
                                    <input type="number" class="form-control quantity-input" 
                                          name="ingredients[0][quantity]" 
                                          id="quantityInput0"
                                          placeholder="Mennyiség" 
                                          min="0" 
                                          step="0.1" 
                                          required>
                                    <span class="input-group-text unit-label" id="unitLabel0">Egys.</span>
                                </div>
                                <input type="hidden" name="ingredients[0][unit_id]" class="unit-id"
                                       id="unitId0" value="">
                                <input type="hidden" name="ingredients[0][unit]" class="unit-abbr"
                                       id="unitAbbr0" value="">
                            </div>
                            <div class="col-3">
                                <label class="form-label ingredient-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger w-100 remove-ingredient">
                                    <i class="bi bi-trash me-2"></i>Törlés
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addIngredientBtn" class="btn btn-outline-secondary mt-2">
                        <i class="bi bi-plus-circle"></i> További hozzávaló
                    </button>
                </div>
                
                <div class="mb-4">
                    <label for="recipeInstructions" class="form-label">Elkészítési útmutató *</label>
                    <textarea class="form-control" id="recipeInstructions" name="instructions" rows="6"
                              required></textarea>
                    <div class="form-text">Részletes leírás a recept elkészítéséről</div>
                </div>
                
                <div class="d-md-flex justify-content-between recipe-form-buttons">
                    <button type="submit" class="btn btn-success mb-2 mb-md-0 w-100 w-md-auto">
                        <i class="bi bi-check-circle"></i> Recept beküldése
                    </button>
                    <a href="/recipes" class="btn btn-outline-secondary w-100 w-md-auto">Mégsem</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module" src="/js/recipeFunctions.js"></script>
