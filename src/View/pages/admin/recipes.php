<?php
// src/View/pages/admin/recipes.php

$currentPage = $page  ?? 1;
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');        // pl. /admin/recipes
$queryBase = '?per_page=' . urlencode($perPage) . '&page=';
?>
<div class="container py-4">
    <h1 class="mb-4">Beküldött receptek kezelése</h1>

    <?php if (isset($recipes) && count($recipes) > 0) : ?>
        <div class="table-responsive shadow-sm rounded-2">
            <table class="table table-striped mb-0">
                <thead class="table">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Kép</th>
                    <th scope="col">Név</th>
                    <th scope="col">Kategória</th>
                    <th scope="col">Beküldő</th>
                    <th scope="col">Dátum</th>
                    <th scope="col">Státusz</th>
                    <th scope="col">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recipes as $recipe) : ?>
                    <tr>
                        <th scope="row"><?= htmlspecialchars($recipe['id'], ENT_QUOTES) ?></th>
                        <td>
                            <?php if (!empty($recipe['image'])) : ?>
                                <img src="<?= htmlspecialchars($recipe['image'], ENT_QUOTES) ?>"
                                     alt="Recipe thumbnail"
                                     class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                            <?php else : ?>
                                <span class="text-muted">Nincs kép</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($recipe['name'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($recipe['category'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($recipe['created_by'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($recipe['created_at'], ENT_QUOTES) ?></td>
                        <td>
                            <?php if ($recipe['status'] === 'pending') : ?>
                                <span class="badge bg-warning text-dark">Függőben</span>
                            <?php elseif ($recipe['status'] === 'approved') : ?>
                                <span class="badge bg-success">Jóváhagyva</span>
                            <?php elseif ($recipe['status'] === 'rejected') : ?>
                                <span class="badge bg-danger">Elutasítva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/recipe/<?= $recipe['id'] ?>" class="btn btn-sm btn-outline-primary me-1"
                               target="_blank">
                                <i class="bi bi-eye"></i> Megtekintés
                            </a>
                            
                            <?php if ($recipe['status'] === 'pending') : ?>
                                <form method="post" action="/admin/recipes/approve" class="d-inline">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                        <i class="bi bi-check-circle"></i> Jóváhagyás
                                    </button>
                                </form>
                                
                                <form method="post" action="/admin/recipes/reject" class="d-inline">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="id" value="<?= $recipe['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger me-1">
                                        <i class="bi bi-x-circle"></i> Elutasítás
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="/admin/recipes/delete" class="d-inline" 
                                  onsubmit="return confirm('Biztosan törölni szeretnéd ezt a receptet?');">
                                <input type="hidden" name="csrf"
                                       value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                <input type="hidden" name="id" value="<?= $recipe['id'] ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-info shadow-sm rounded-2" role="alert">
            Nincsenek beküldött receptek.
        </div>
    <?php endif; ?>

    <?php if (($totalPages ?? 0) > 1) : ?>
        <nav aria-label="Oldalak közti navigáció" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Előző -->
                <li class="page-item<?= $currentPage <= 1 ? ' disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= $baseUrl . $queryBase . max(1, $currentPage - 1) ?>"
                       tabindex="-1">Előző</a>
                </li>

                <!-- Oldalszámok -->
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item<?= $i === $currentPage ? ' active' : '' ?>">
                        <a class="page-link"
                           href="<?= $baseUrl . $queryBase . $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Következő -->
                <li class="page-item<?= $currentPage >= $totalPages ? ' disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= $baseUrl . $queryBase . min($totalPages, $currentPage + 1) ?>">
                        Következő
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
