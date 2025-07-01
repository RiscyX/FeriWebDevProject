// public/js/fridgeFunctions.js

export function initFridgeModal() {
    const itemNameInput  = document.getElementById('itemName');
    const quantityLabel  = document.querySelector("label[for='itemQuantity']");
    const editIdInput    = document.getElementById('editItemId');
    const modalTitle     = document.getElementById('addItemModalLabel');
    const ingredientMap  = new Map();
    const form           = document.getElementById('addItemForm');

    console.log('🥶 initFridgeModal loaded');

    // 1) Autocomplete betöltése
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
            console.error('Autocomplete hiba:', err);
        }
    });

    // 1b) Change esemény autocomplete-hoz
    itemNameInput.addEventListener('change', () => {
        const key = itemNameInput.value.trim().toLowerCase();
        const ing = ingredientMap.get(key);
        quantityLabel.textContent = ing
            ? `Mennyiség (${ing.unit_name})`
            : 'Mennyiség';
    });

    // 2) Edit gombok előtöltése
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id       = btn.dataset.id;
            const name     = btn.dataset.name;
            const quantity = btn.dataset.quantity;
            const unitName = btn.dataset.unitName;
            const unitAbbr = btn.dataset.unitAbbr;
            ingredientMap.clear();
            ingredientMap.set(name.toLowerCase(), {
                id:        parseInt(id, 10),
                unit_name: unitName,
                unit_abbr: unitAbbr
            });
            editIdInput.value = id;
            itemNameInput.value = name;
            document.getElementById('itemQuantity').value = quantity;
            quantityLabel.textContent = unitName
                ? `Mennyiség (${unitName})`
                : 'Mennyiség';
            modalTitle.textContent = 'Tétel szerkesztése';
        });
    });

    // 3) Delete gombok kezelése
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            console.log('delete clicked', btn.dataset.id);
            const id = btn.dataset.id;
            if(!window.confirm("Biztosan torolni szeretned?")){
                return;
            }
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta?.content;
            console.log('CSRF token:', csrf);
            try {
                const res = await fetch(`/api/fridge/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': csrf || ''
                    }
                });
                console.log('fetch returned', res.status);
                const data = await res.json();
                console.log('response JSON:', data);
                if (res.ok) location.reload();
                else        alert('Hiba: ' + (data.error || 'Törlés sikertelen'));
            } catch (err) {
                console.error('fetch threw', err);
                alert('Hálózati hiba történt.');
            }
        });
    });

    // 4) Új tétel gomb reset
    document.querySelector('button[data-bs-target="#addItemModal"]:not(.edit-item-btn)')
        .addEventListener('click', () => {
            editIdInput.value = '';
            form.reset();
            quantityLabel.textContent = 'Mennyiség';
            modalTitle.textContent = 'Új hűtőelem hozzáadása';
        });

    // 5) Submit: POST vagy PUT
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const raw    = itemNameInput.value.trim().toLowerCase();
        const ing    = ingredientMap.get(raw);
        const qty    = parseInt(document.getElementById('itemQuantity').value, 10);
        const csrf   = document.querySelector('meta[name="csrf-token"]').content;
        const editId = editIdInput.value;
        if (!ing || !ing.id || qty < 1) {
            alert('Kérlek válassz érvényes hozzávalót és adj meg mennyiséget.');
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
            else        alert('Hiba: ' + (data.error || 'Ismeretlen hiba'));
        } catch (err) {
            console.error('submit threw', err);
            alert('Hálózati hiba történt.');
        }
    });
}

// Automatikus init
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFridgeModal);
} else {
    initFridgeModal();
}
