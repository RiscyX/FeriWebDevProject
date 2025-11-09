<?php

$title ??= 'Új jelszó beállítása';

if (!empty($error)) : ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?= $formHtml ?>