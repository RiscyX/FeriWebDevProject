/**
 * Recipe submission functionality
 * A hozzávalók automatikus kiegészítéséért és a mértékegységek kezeléséért felelős kód
 */

/**
 * Inicializálja a hozzávalók kezelését
 */
function initIngredientFunctionality() {
    const ingredientsContainer = document.getElementById('ingredientsContainer');
    const addIngredientBtn = document.getElementById('addIngredientBtn');
    const ingredientMaps = new Map(); // Tárolja az összes hozzávaló-indexhez tartozó adatokat
    let ingredientIndex = 1;
    
    // A már létező hozzávaló mezőkhöz autocomplete inicializálás
    document.querySelectorAll('.ingredient-name').forEach(input => {
        setupIngredientAutocomplete(input, ingredientMaps);
    });
    
    // Új hozzávaló sor hozzáadása
    addIngredientBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'row mb-2 ingredient-row';
        newRow.innerHTML = `
            <div class="col-md-5 col-12">
                <label class="form-label ingredient-label">Hozzávaló</label>
                <input type="text" class="form-control ingredient-name" 
                       list="ingredientList${ingredientIndex}" 
                       name="ingredients[${ingredientIndex}][name]" 
                       data-index="${ingredientIndex}"
                       placeholder="Hozzávaló neve" 
                       autocomplete="off"
                       required>
                <datalist id="ingredientList${ingredientIndex}"></datalist>
                <input type="hidden" name="ingredients[${ingredientIndex}][ingredient_id]" class="ingredient-id" id="ingredientId${ingredientIndex}" value="">
            </div>
            <div class="col-md-4 col-8">
                <label class="form-label ingredient-label">Mennyiség</label>
                <div class="input-group">
                    <input type="number" class="form-control quantity-input" 
                          name="ingredients[${ingredientIndex}][quantity]" 
                          id="quantityInput${ingredientIndex}"
                          placeholder="Mennyiség" 
                          min="0" 
                          step="0.1" 
                          required>
                    <span class="input-group-text unit-label" id="unitLabel${ingredientIndex}">Egys.</span>
                </div>
                <input type="hidden" name="ingredients[${ingredientIndex}][unit_id]" class="unit-id" id="unitId${ingredientIndex}" value="">
                <input type="hidden" name="ingredients[${ingredientIndex}][unit]" class="unit-abbr" id="unitAbbr${ingredientIndex}" value="">
            </div>
            <div class="col-md-3 col-4">
                <label class="form-label ingredient-label">&nbsp;</label>
                <button type="button" class="btn btn-danger w-100 remove-ingredient">
                    <i class="bi bi-trash me-2"></i>Törlés
                </button>
            </div>
        `;
        ingredientsContainer.appendChild(newRow);
        
        // Az új sorban lévő hozzávaló mezőhöz autocomplete beállítása
        setupIngredientAutocomplete(newRow.querySelector('.ingredient-name'), ingredientMaps);
        
        // Hozzávalók törlése
        newRow.querySelector('.remove-ingredient').addEventListener('click', function() {
            if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                this.closest('.ingredient-row').remove();
            } else {
                alert('Legalább egy hozzávalónak kell lennie!');
            }
        });
        
        ingredientIndex++;
    });
    
    // Kezdeti hozzávaló sor törlő gombja és minden más hozzávalói elem beállítása
    document.querySelectorAll('.remove-ingredient').forEach(btn => {
        btn.addEventListener('click', function() {
            if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                this.closest('.ingredient-row').remove();
            } else {
                alert('Legalább egy hozzávalónak kell lennie!');
            }
        });
    });
    
    // Korrigáljuk az első hozzávaló sor mobilos megjelenítését is
    const fixExistingRows = () => {
        // Megkeressük az első hozzávaló sort
        const firstRow = document.querySelector('.ingredient-row');
        if (firstRow) {
            // Ellenőrizzük, hogy helyesen vannak-e az osztályok beállítva
            if (!firstRow.classList.contains('mb-2')) firstRow.classList.add('mb-2');
            
            // Biztosítsuk, hogy minden elem a megfelelő osztályt használja
            const labels = firstRow.querySelectorAll('label');
            labels.forEach(label => {
                // Minden címkének legyen "ingredient-label" osztálya
                if (!label.classList.contains('ingredient-label')) {
                    label.classList.add('ingredient-label');
                }
                // Távolítsuk el a d-md-none osztályt, ha van ilyen
                if (label.classList.contains('d-md-none')) {
                    label.classList.remove('d-md-none');
                }
            });
            
            // Ellenőrizzük, hogy a megfelelő oszlop osztályok vannak-e
            const nameCol = firstRow.querySelector('div:nth-child(1)');
            const qtyCol = firstRow.querySelector('div:nth-child(2)');
            const btnCol = firstRow.querySelector('div:nth-child(3)');
            
            if (nameCol && !nameCol.classList.contains('col-md-5')) {
                nameCol.className = 'col-md-5 col-12';
            }
            
            if (qtyCol && !qtyCol.classList.contains('col-md-4')) {
                qtyCol.className = 'col-md-4 col-8';
            }
            
            if (btnCol && !btnCol.classList.contains('col-md-3')) {
                btnCol.className = 'col-md-3 col-4';
            }
        }
    };
    
    // Oldalbetöltéskor is futtassuk le
    fixExistingRows();
    
    // Form elküldés előtt validálás
    const recipeForm = document.querySelector('form[action="/recipe/submit"]');
    if (recipeForm) {
        recipeForm.addEventListener('submit', function(e) {
            const ingredientInputs = document.querySelectorAll('.ingredient-name');
            let valid = true;
            
            ingredientInputs.forEach(input => {
                const index = input.dataset.index;
                const idInput = document.getElementById(`ingredientId${index}`);
                
                if (!idInput.value) {
                    input.setCustomValidity('Kérlek válassz érvényes hozzávalót a listából');
                    valid = false;
                } else {
                    input.setCustomValidity('');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Kérlek ellenőrizd a hozzávalókat! Minden hozzávalót a legördülő listából kell kiválasztani.');
            }
        });
    }
}

/**
 * Beállítja az autocomplete funkciót egy hozzávaló beviteli mezőhöz
 * @param {HTMLInputElement} inputElement - A hozzávaló beviteli mező
 * @param {Map} ingredientMaps - A hozzávaló adatok tárolására szolgáló Map
 */
function setupIngredientAutocomplete(inputElement, ingredientMaps) {
    const index = inputElement.dataset.index;
    const datalistId = `ingredientList${index}`;
    const datalist = document.getElementById(datalistId);
    const quantityLabel = document.getElementById(`unitLabel${index}`);
    const ingredientIdInput = document.getElementById(`ingredientId${index}`);
    const unitIdInput = document.getElementById(`unitId${index}`);
    const unitAbbrInput = document.getElementById(`unitAbbr${index}`);
    
    // Hozzávalók térképének inicializálása ehhez az indexhez
    if (!ingredientMaps.has(index)) {
        ingredientMaps.set(index, new Map());
    }
    
    // Autocomplete betöltése írás közben
    inputElement.addEventListener('input', async () => {
        const query = inputElement.value.trim();
        if (query.length < 2) return;
        
        try {
            const response = await fetch('/api/ingredients?search=' + encodeURIComponent(query));
            if (!response.ok) {
                throw new Error('Hálózati hiba a hozzávalók lekérdezésekor');
            }
            
            const data = await response.json();
            
            // Datalist és ingredientMap frissítése
            datalist.innerHTML = '';
            ingredientMaps.get(index).clear();
            
            data.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.name;
                datalist.appendChild(option);
                
                ingredientMaps.get(index).set(item.name.toLowerCase(), {
                    id: item.id,
                    unit_id: item.unit_id || 0,
                    unit_name: item.unit_name || '',
                    unit_abbr: item.unit_abbr || ''
                });
            });
        } catch (error) {
            console.error('Hiba a hozzávalók lekérdezésekor:', error);
        }
    });
    
    // Hozzávaló kiválasztásakor a mértékegység frissítése
    inputElement.addEventListener('change', () => {
        const selectedName = inputElement.value.trim().toLowerCase();
        const ingredientMap = ingredientMaps.get(index);
        const ingredient = ingredientMap?.get(selectedName);
        
        if (ingredient) {
            // Mértékegység beállítása
            quantityLabel.textContent = ingredient.unit_abbr || 'Egys.';
            ingredientIdInput.value = ingredient.id;
            unitIdInput.value = ingredient.unit_id || '';
            unitAbbrInput.value = ingredient.unit_abbr || '';
            
            // Custom validálás törlése
            inputElement.setCustomValidity('');
        } else {
            // Ha nem létező hozzávalót írtak be
            quantityLabel.textContent = 'Egys.';
            ingredientIdInput.value = '';
            unitIdInput.value = '';
            unitAbbrInput.value = '';
            
            // Custom validálás beállítása
            inputElement.setCustomValidity('Kérlek válassz érvényes hozzávalót a listából');
        }
    });
}

// Ha közvetlenül a HTML-be van importálva, akkor azonnal inicializál
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initIngredientFunctionality);
} else {
    initIngredientFunctionality();
}

// Exportálás modulos használathoz
export { initIngredientFunctionality };
