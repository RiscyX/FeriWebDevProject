/**
 * Recipe submission functionality
 * Code responsible for ingredient auto-completion and unit management
 */

/**
 * Initializes ingredient management
 */
function initIngredientFunctionality() {
    const ingredientsContainer = document.getElementById('ingredientsContainer');
    const addIngredientBtn = document.getElementById('addIngredientBtn');
    const ingredientMaps = new Map(); // Stores data for all ingredient indexes
    let ingredientIndex = 1;
    
    // Initialize autocomplete for existing ingredient fields
    document.querySelectorAll('.ingredient-name').forEach(input => {
        setupIngredientAutocomplete(input, ingredientMaps);
    });
    
    // Add new ingredient row
    addIngredientBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'row mb-2 ingredient-row';
        newRow.innerHTML = `
            <div class="col-md-5 col-12">
                <label class="form-label ingredient-label">Ingredient</label>
                <input type="text" class="form-control ingredient-name" 
                       list="ingredientList${ingredientIndex}" 
                       name="ingredients[${ingredientIndex}][name]" 
                       data-index="${ingredientIndex}"
                       placeholder="Ingredient name" 
                       autocomplete="off"
                       required>
                <datalist id="ingredientList${ingredientIndex}"></datalist>
                <input type="hidden" name="ingredients[${ingredientIndex}][ingredient_id]" class="ingredient-id" id="ingredientId${ingredientIndex}" value="">
            </div>
            <div class="col-md-4 col-8">
                <label class="form-label ingredient-label">Quantity</label>
                <div class="input-group">
                    <input type="number" class="form-control quantity-input" 
                          name="ingredients[${ingredientIndex}][quantity]" 
                          id="quantityInput${ingredientIndex}"
                          placeholder="Quantity" 
                          min="0" 
                          step="0.1" 
                          required>
                    <span class="input-group-text unit-label" id="unitLabel${ingredientIndex}">Unit</span>
                </div>
                <input type="hidden" name="ingredients[${ingredientIndex}][unit_id]" class="unit-id" id="unitId${ingredientIndex}" value="">
                <input type="hidden" name="ingredients[${ingredientIndex}][unit]" class="unit-abbr" id="unitAbbr${ingredientIndex}" value="">
            </div>
            <div class="col-md-3 col-4">
                <label class="form-label ingredient-label">&nbsp;</label>
                <button type="button" class="btn btn-danger w-100 remove-ingredient">
                    <i class="bi bi-trash me-2"></i>Delete
                </button>
            </div>
        `;
        ingredientsContainer.appendChild(newRow);
        
        // Set up autocomplete for the ingredient field in the new row
        setupIngredientAutocomplete(newRow.querySelector('.ingredient-name'), ingredientMaps);
        
        // Ingredient deletion
        newRow.querySelector('.remove-ingredient').addEventListener('click', function() {
            if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                this.closest('.ingredient-row').remove();
            } else {
                alert('There must be at least one ingredient!');
            }
        });
        
        ingredientIndex++;
    });
    
    // Setup delete button for initial ingredient row and all other ingredient elements
    document.querySelectorAll('.remove-ingredient').forEach(btn => {
        btn.addEventListener('click', function() {
            if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                this.closest('.ingredient-row').remove();
            } else {
                alert('There must be at least one ingredient!');
            }
        });
    });
    
    // Fix mobile display for the first ingredient row as well
    const fixExistingRows = () => {
        // Find the first ingredient row
        const firstRow = document.querySelector('.ingredient-row');
        if (firstRow) {
            // Check if classes are set correctly
            if (!firstRow.classList.contains('mb-2')) firstRow.classList.add('mb-2');
            
            // Ensure all elements use the appropriate class
            const labels = firstRow.querySelectorAll('label');
            labels.forEach(label => {
                // Every label should have the "ingredient-label" class
                if (!label.classList.contains('ingredient-label')) {
                    label.classList.add('ingredient-label');
                }
                // Remove d-md-none class if it exists
                if (label.classList.contains('d-md-none')) {
                    label.classList.remove('d-md-none');
                }
            });
            
            // Check if the correct column classes are present
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
    
    // Run at page load as well
    fixExistingRows();
    
    // Form validation before submission
    const recipeForm = document.querySelector('form[action="/recipe/submit"]');
    if (recipeForm) {
        recipeForm.addEventListener('submit', function(e) {
            const ingredientInputs = document.querySelectorAll('.ingredient-name');
            let valid = true;
            
            ingredientInputs.forEach(input => {
                const index = input.dataset.index;
                const idInput = document.getElementById(`ingredientId${index}`);
                
                if (!idInput.value) {
                    input.setCustomValidity('Please select a valid ingredient from the list');
                    valid = false;
                } else {
                    input.setCustomValidity('');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please check the ingredients! All ingredients must be selected from the dropdown list.');
            }
        });
    }
}

/**
 * Sets up the autocomplete functionality for an ingredient input field
 * @param {HTMLInputElement} inputElement - The ingredient input field
 * @param {Map} ingredientMaps - Map for storing ingredient data
 */
function setupIngredientAutocomplete(inputElement, ingredientMaps) {
    const index = inputElement.dataset.index;
    const datalistId = `ingredientList${index}`;
    const datalist = document.getElementById(datalistId);
    const quantityLabel = document.getElementById(`unitLabel${index}`);
    const ingredientIdInput = document.getElementById(`ingredientId${index}`);
    const unitIdInput = document.getElementById(`unitId${index}`);
    const unitAbbrInput = document.getElementById(`unitAbbr${index}`);
    
    // Initialize ingredient map for this index
    if (!ingredientMaps.has(index)) {
        ingredientMaps.set(index, new Map());
    }
    
    // Load autocomplete while typing
    inputElement.addEventListener('input', async () => {
        const query = inputElement.value.trim();
        if (query.length < 2) return;
        
        try {
            const response = await fetch('/api/ingredients?search=' + encodeURIComponent(query));
            if (!response.ok) {
                throw new Error('Network error while fetching ingredients');
            }
            
            const data = await response.json();
            
            // Update datalist and ingredientMap
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
            console.error('Error fetching ingredients:', error);
        }
    });
    
    // Update unit when ingredient is selected
    inputElement.addEventListener('change', () => {
        const selectedName = inputElement.value.trim().toLowerCase();
        const ingredientMap = ingredientMaps.get(index);
        const ingredient = ingredientMap?.get(selectedName);
        
        if (ingredient) {
            // Set unit
            quantityLabel.textContent = ingredient.unit_abbr || 'Unit';
            ingredientIdInput.value = ingredient.id;
            unitIdInput.value = ingredient.unit_id || '';
            unitAbbrInput.value = ingredient.unit_abbr || '';
            
            // Clear custom validation
            inputElement.setCustomValidity('');
        } else {
            // If a non-existent ingredient was entered
            quantityLabel.textContent = 'Unit';
            ingredientIdInput.value = '';
            unitIdInput.value = '';
            unitAbbrInput.value = '';
            
            // Set custom validation
            inputElement.setCustomValidity('Please select a valid ingredient from the list');
        }
    });
}

// If directly imported into HTML, initialize immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initIngredientFunctionality);
} else {
    initIngredientFunctionality();
}

// Export for modular usage
export { initIngredientFunctionality };
