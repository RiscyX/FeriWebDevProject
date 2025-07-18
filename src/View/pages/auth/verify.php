<?php

/** @var string $message
 *  @var string $type
 */
$title ??= 'E-mail megerősítése';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="alert alert-<?= htmlspecialchars($type) ?> d-flex flex-column gap-3" role="alert">
                <p class="mb-0"><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
                <div class="text-end">
                    <a href="/login" class="btn btn-primary">Bejelentkezés</a>
                </div>
            </div>
        </div>
    </div>
</div>
