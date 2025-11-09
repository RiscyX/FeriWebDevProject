<?php
// src/View/pages/profile.php
?>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Profil információk</h5>
            </div>
            <div class="card-body">
                <h4><?= htmlspecialchars($user['username'] ?? 'Felhasználó') ?></h4>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                <p><strong>Regisztrált:</strong> <?= (new \DateTime($user['created_at'] ??
                        'now'))->format('Y.m.d.') ?>
                </p>

                <div class="mt-3">
                    <a href="/fridge" class="btn btn-success btn-md">
                        <i class="bi bi-box"></i> Hűtőszekrényed megtekintése
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Kedvenc receptjeid</h5>
            </div>
            <div class="card-body">
                <?php if (empty($favoriteRecipes)) : ?>
                    <div class="alert alert-info">
                        <p>Még nem adtál hozzá recepteket a kedvenceidhez.</p>
                        <a href="/recipes" class="btn btn-primary mt-2">Receptek böngészése</a>
                    </div>
                <?php else : ?>
                    <div class="row">
                        <?php foreach ($favoriteRecipes as $recipe) : ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($recipe['image']) ?>" class="card-img-top recipe-img"
                                         alt="<?= htmlspecialchars($recipe['name']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="/recipe/<?= $recipe['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($recipe['name']) ?>
                                            </a>
                                        </h5>
                                        <p class="card-text"><?= htmlspecialchars($recipe['short_description']) ?></p>
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <a href="/recipe/<?= $recipe['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">Részletek</a>

                                        <form action="/profile/favorites/remove" method="post">
                                            <input type="hidden" name="csrf"
                                                   value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                            <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-heart-fill"></i> Eltávolítás
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
