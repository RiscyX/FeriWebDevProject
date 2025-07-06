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
                <thead class="table">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Felhasználónév</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Szerepkör</th>
                    <th scope="col">Státusz</th>
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
                            <?php if ((int)($user['is_banned'] ?? 0) === 1) : ?>
                                <span class="badge bg-danger">Bannolt</span>
                            <?php else : ?>
                                <span class="badge bg-success">Aktív</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)($user['is_banned'] ?? 0) === 1) : ?>
                                <form method="post" action="/admin/users/unban" class="d-inline">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success me-2"
                                            onclick="return confirm('Biztosan feloldod a felhasználó bannolását?');">
                                        Bannolás feloldása</button>
                                </form>
                            <?php else : ?>
                                <form method="post" action="/admin/users/ban" class="d-inline">
                                    <input type="hidden" name="csrf"
                                           value="<?= \WebDevProject\Security\Csrf::token() ?>">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-warning me-2"
                                            onclick="return confirm('Biztosan bannolod a felhasználót?');">
                                        Felhasználó bannolása</button>
                                </form>
                            <?php endif; ?>
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

</div>


    <?php if (($totalPages ?? 0) > 1) : ?>
        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Oldalak közti navigáció">
                <ul class="pagination">
                    <!-- Előző -->
                    <li class="page-item<?= $currentPage <= 1 ? ' disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= $baseUrl . $queryBase . max(1, $currentPage - 1) ?>"
                           aria-label="Előző">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="visually-hidden">Előző</span>
                        </a>
                    </li>

                    <!-- Oldalszámok -->
                    <?php
                    // Korlátozzuk a lapozási linkek számát
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    // Ha az elejéről vagy végéről vannak oldalak, amelyek kiesnek a tartományból, akkor kompenzálunk
                    if ($startPage > 1) {
                        echo '<li class="page-item">
                                <a class="page-link" href="' . $baseUrl . $queryBase . '1">1</a></li>';

                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo '<li class="page-item' . ($currentPage == $i ? ' active' : '') . '">
                            <a class="page-link" href="' . $baseUrl . $queryBase . $i . '">' . $i . '</a>
                        </li>';
                    }

                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }

                        echo '<li class="page-item">
                <a class="page-link" href="' . $baseUrl . $queryBase . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>

                    <!-- Következő -->
                    <li class="page-item<?= $currentPage >= $totalPages ? ' disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= $baseUrl . $queryBase . min($totalPages, $currentPage + 1) ?>"
                           aria-label="Következő">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="visually-hidden">Következő</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <?php if (($total ?? 0) > 0) : ?>
        <div class="text-center text-muted mt-3 mb-5">
            <small>Összesen <?= $total ?> felhasználó, <?= $totalPages ?> oldal</small>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
