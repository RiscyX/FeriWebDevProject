<?php
// src/View/pages/admin/users.php

$currentPage  = $page  ?? 1;
$baseUrl      = strtok($_SERVER['REQUEST_URI'], '?');        // pl. /admin/users
$queryBase    = '?per_page=' . urlencode($perPage) . '&page=';
?>
<div class="container py-4">
    <h1 class="mb-4">Felhasználók kezelése</h1>

    <?php if (isset($users) && count($users) > 0) : ?>
        <div class="table-responsive shadow-sm rounded-2">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Felhasználónév</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Szerepkör</th>
                    <th scope="col">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <th scope="row"><?= htmlspecialchars($user['id'], ENT_QUOTES) ?></th>
                        <td><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></td>
                        <td>
                            <?php if ((int)($user['role'] ?? 0) === 1) : ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else : ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/users/edit?id=<?= $user['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-2">Szerkesztés</a>
                            <form method="post" action="/admin/users/delete" class="d-inline">
                                <input type="hidden" name="csrf" value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Biztosan törlöd?');">Törlés</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-info" role="alert">
            Nincsenek felhasználók a rendszerben.
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="/admin/users/create" class="btn btn-success rounded-pill shadow-sm">Új felhasználó létrehozása</a>
        <a href="/admin" class="btn btn-secondary rounded-pill shadow-sm ms-2">Vissza az admin főoldalra</a>
    </div>
</div>


    <?php if (($totalPages ?? 0) > 1) : ?>
        <nav aria-label="Oldalak közti navigáció">
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
