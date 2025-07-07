// public/js/fridgeFunctions.js

export function initFridgeModal() {
    const itemNameInput  = document.getElementById('itemName');
    const quantityLabel  = document.querySelector("label[for='itemQuantity']");
    const editIdInput    = document.getElementById('editItemId');
    const modalTitle     = document.getElementById('addItemModalLabel');
    const ingredientMap  = new Map();
    const form           = document.getElementById('addItemForm');

    console.log('🥶 initFridgeModal loaded');

    // 1) Load autocomplete
    itemNameInput.addEventListener('input', async () => {
        const q = itemNameInput.value.trim();
        if (q.length < 2) return;
        try {
            const res = await fetch('/api/ingredients?search=' + encodeURIComponent(q));
            const { data } = await res.json();
            const list = document.getElementById('ingredientList');
            list.innerHTML = '';
            ingredientMap.clear();
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.name;
                list.appendChild(option);
                ingredientMap.set(item.name.toLowerCase(), {
                    id:        item.id,
                    unit_name: item.unit_name,
                    unit_abbr: item.unit_abbr
                });
            });
        } catch (err) {
            console.error('Autocomplete error:', err);
        }
    });

    // 1b) Change event for autocomplete
    itemNameInput.addEventListener('change', () => {
        const key = itemNameInput.value.trim().toLowerCase();
        const ing = ingredientMap.get(key);
        quantityLabel.textContent = ing
            ? `Quantity (${ing.unit_name})`
            : 'Quantity';
    });

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const fridgeItemId   = btn.dataset.id;              // fridge_items.id
            const ingredientId   = btn.dataset.ingredientId;    // ingredient.id - new
            const name           = btn.dataset.name;
            const quantity       = btn.dataset.quantity;
            const unitName       = btn.dataset.unitName;
            const unitAbbr       = btn.dataset.unitAbbr;

            // completely clear the map and datalist
            ingredientMap.clear();
            document.getElementById('ingredientList').innerHTML = '';

            // now correctly add the ingredient ID to the map
            ingredientMap.set(name.toLowerCase(), {
                id:        parseInt(ingredientId, 10),
                unit_name: unitName,
                unit_abbr: unitAbbr
            });

            // hidden input for the fridge_item PK
            editIdInput.value      = fridgeItemId;
            itemNameInput.value    = name;
            document.getElementById('itemQuantity').value = quantity;
            quantityLabel.textContent = unitName
                ? `Quantity (${unitName})`
                : 'Quantity';
            modalTitle.textContent = 'Edit Item';
        });
    });

    // 3) Handle delete buttons
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;  // ← add this, otherwise id will be undefined
            if (!window.confirm("Are you sure you want to delete?")) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch(`/api/fridge/${id}`, {
                    method: 'DELETE',                 // DELETE method
                    headers: {
                        'X-CSRF-Token': csrf || ''
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    // if you don't want to reload:
                    btn.closest('tr').remove();
                } else {
                    alert('Error: ' + (data.error || 'Deletion failed'));
                }
            } catch (err) {
                console.error('Error during deletion:', err);
                alert('A network error occurred.');
            }
        });
    });


// 4) New item button reset - also clear the map and datalist here
    document.querySelector('button[data-bs-target="#addItemModal"]:not(.edit-item-btn)')
        .addEventListener('click', () => {
            editIdInput.value = '';
            form.reset();
            quantityLabel.textContent = 'Quantity';
            modalTitle.textContent = 'Add New Fridge Item';

            ingredientMap.clear();
            document.getElementById('ingredientList').innerHTML = '';
        });
    // 5) Submit: POST or PUT
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const raw    = itemNameInput.value.trim().toLowerCase();
        const ing    = ingredientMap.get(raw);
        const qty    = parseInt(document.getElementById('itemQuantity').value, 10);
        const csrf   = document.querySelector('meta[name="csrf-token"]').content;
        const editId = editIdInput.value;
        if (!ing || !ing.id || qty < 1) {
            alert('Please select a valid ingredient and specify a quantity.');
            return;
        }
        const url    = editId ? `/api/fridge/${editId}` : '/api/fridge';
        const method = editId ? 'PUT' : 'POST';
        const body   = JSON.stringify({ ingredient_id: ing.id, quantity: qty });
        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf,
                },
                body
            });
            const data = await res.json();
            if (res.ok) location.reload();
            else        alert('Error: ' + (data.error || 'Unknown error'));
        } catch (err) {
            console.error('submit threw', err);
            alert('A network error occurred.');
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFridgeModal);
} else {
    initFridgeModal();
}
