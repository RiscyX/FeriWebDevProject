<?php
var_dump($items);
// src/View/pages/admin/users.php
?>
<div class="container py-4">
    <h1 class="mb-4">Hűtőszekrényem</h1>

    <?php if (!empty($items)) : ?>
        <div class="table-responsive shadow-sm rounded-2 mb-4">
            <table class="table table-hover text-center align-middle mb-0">
                <thead class="table">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Tétel</th>
                    <th scope="col">Mennyiség</th>
                    <th scope="col">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $idx => $item) : ?>
                    <tr>
                        <th scope="row"><?= $idx + 1 ?></th>
                        <td><?= htmlspecialchars($item['ingredient_name'], ENT_QUOTES) ?></td>
                        <td><?=
                            (int)htmlspecialchars($item['quantity'], ENT_QUOTES) . " " .
                            htmlspecialchars($item['unit_abbr'], ENT_QUOTES) ?></td>
                        <td>
                            <button
                                    class="btn btn-sm btn-secondary me-2 edit-item-btn"
                                    data-id="<?= $item['id'] ?>"
                                    data-ingredient-id="<?= (int)$item['ingredient_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['ingredient_name'], ENT_QUOTES) ?>"
                                    data-unit-name="<?= htmlspecialchars($item['unit_name'], ENT_QUOTES) ?>"
                                    data-quantity="<?= (int)$item['quantity'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addItemModal">
                                Szerkesztés
                            </button>
                            <button
                                    class="btn btn-sm btn-danger delete-item-btn"
                                    data-id="<?= $item['id'] ?>"
                                    data-ingredient-id="<?= (int)$item['ingredient_id'] ?>"
                            >Törlés
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-info shadow-sm rounded-2" role="alert">
            A hűtőd pillanatnyilag üres.
        </div>
    <?php endif; ?>

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable w-100 w-sm-75">
            <div class="modal-content">
                <form id="addItemForm">
                    <input type="hidden" id="editItemId" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Új hűtőelem hozzáadása</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="itemName" class="form-label">Tétel</label>
                            <input type="text" class="form-control" id="itemName" list="ingredientList" required>
                            <datalist id="ingredientList"></datalist>
                        </div>
                        <div class="mb-3">
                            <label for="itemQuantity" class="form-label">Mennyiség</label>
                            <input type="number" class="form-control"
                                   id="itemQuantity" name="quantity" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Mentés</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mégsem</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-success rounded-pill shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addItemModal">
            Új tétel hozzáadása
        </button>
        <a href="/admin" class="btn btn-secondary rounded-pill shadow-sm">Vissza az adminhoz</a>
    </div>
</div>