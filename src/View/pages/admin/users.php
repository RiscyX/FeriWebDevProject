<?php
// admin/users.php

// Betöltjük az adatbázis-konfigurációt (javított útvonal)
require_once __DIR__ . '/../../../config/db_config.php';

try {
    // A db_config.php $pdo változót hoz létre
    $db = $pdo;
    
    // Oldalankénti elemek száma
    $limit = 10;
    // Aktuális oldal – ha nincs, az 1
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($currentPage - 1) * $limit;

    // Összes felhasználó lekérdezése a lapozáshoz
    $totalUsersStmt = $db->query('SELECT COUNT(*) FROM users');
    $totalUsers = (int) $totalUsersStmt->fetchColumn();
    $totalPages = (int) ceil($totalUsers / $limit);

    // A megjelenítendő felhasználók lekérése (javított SQL szintaxis)
    $stmt = $db->prepare(
        'SELECT id, username, email, is_banned, role, created_at, email_verified_at 
         FROM users 
         ORDER BY id DESC 
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$limit, $offset]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Hibakezelés
    error_log("Database error in users.php: " . $e->getMessage());
    $users = [];
    $totalPages = 0;
    $totalUsers = 0;
    $currentPage = 1;
}

// A cím beállítása (ha layoutból használod)
$title ??= 'Felhasználók listája';
?>

<div class="container py-5">
    <!-- Hero szekció -->
    <section class="hero mb-4" style="padding:2.5rem;">
        <h1 class="hero-title">Felhasználók kezelése</h1>
        <p class="hero-desc">
            Itt az összes regisztrált felhasználó adatait találod – státusz, szerepkör és e-mail ellenőrzés.
        </p>
    </section>

    <!-- Felhasználók táblázat -->
    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Felhasználónév</th>
                        <th>E-mail</th>
                        <th>Ellenőrizve</th>
                        <th>Regisztráció</th>
                        <th>Szerepkör</th>
                        <th>Státusz</th>
                        <th>Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['email_verified_at']): ?>
                                    <?= date('Y-m-d', strtotime($user['email_verified_at'])) ?>
                                <?php else: ?>
                                    <span class="text-secondary">–</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['role'] === '1'): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['is_banned'] === '1'): ?>
                                    <span class="badge bg-danger">Tiltott</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aktív</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/admin/users/edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">Szerkeszt</a>
                                <?php if ($user['is_banned'] === '1'): ?>
                                    <a href="/admin/users/unban?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-success">Felold</a>
                                <?php else: ?>
                                    <a href="/admin/users/ban?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger">Tilt</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Nincsenek felhasználók az adatbázisban.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap pagináció -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Oldalak közti navigáció">
      <ul class="pagination justify-content-center">
        <!-- Előző gomb -->
        <li class="page-item<?= $currentPage <= 1 ? ' disabled' : '' ?>">
          <a class="page-link" href="?page=<?= max(1, $currentPage-1) ?>" tabindex="-1">Előző</a>
        </li>

        <!-- Oldalszámok -->
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item<?= $i === $currentPage ? ' active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <!-- Következő gomb -->
        <li class="page-item<?= $currentPage >= $totalPages ? ' disabled' : '' ?>">
          <a class="page-link" href="?page=<?= min($totalPages, $currentPage+1) ?>">Következő</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
</div>