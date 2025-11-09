/**
 * Form validation helper functions
 * FeriWebDevProject - 2025
 */

/**
 * Function responsible for displaying the error message
 * @param {HTMLElement} inputElement - The input element to which we attach the error message
 * @param {string} message - The error message text
 */
function showError(inputElement, message) {
    // Delete the previous error message, if any
    removeError(inputElement);
    
    // Add the error class to the input element
    inputElement.classList.add('is-invalid');
    
    // Create the error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.innerHTML = message;
    
    // Place the error message after the input
    if (inputElement.parentElement.classList.contains('input-group')) {
        // For input groups, place after the parent
        inputElement.parentElement.parentElement.appendChild(errorDiv);
    } else {
        // Otherwise directly after the input
        inputElement.parentElement.appendChild(errorDiv);
    }
}

/**
 * Removes the error message from an input element
 * @param {HTMLElement} inputElement - The input element from which to remove the error message
 */
function removeError(inputElement) {
    inputElement.classList.remove('is-invalid');
    
    // Find and remove the associated error message
    const parent = inputElement.parentElement;
    const grandparent = parent.parentElement;
    
    // Check both possible locations for the error message
    [parent, grandparent].forEach(element => {
        const errorDiv = element.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    });
}

/**
 * Adds the success class on successful validation
 * @param {HTMLElement} inputElement - The input element to mark as valid
 */
function markValid(inputElement) {
    removeError(inputElement);
    inputElement.classList.add('is-valid');
}

/**
 * Email address format validation
 * @param {string} email - The email address to validate
 * @returns {boolean} Whether the email address is valid
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

/**
 * Password strength validation
 * @param {string} password - The password to validate
 * @returns {boolean} Whether the password is strong enough
 */
function isStrongPassword(password) {
    // Minimum 6 characters, at least one number and one letter
    return password.length >= 6 && /[0-9]/.test(password) && /[a-zA-Z]/.test(password);
}

// ============================
// Login form validation
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action="/login"]');
    
    if (loginForm) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        // Input event listeners
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Email address is required');
                } else if (!isValidEmail(this.value)) {
                    showError(this, 'Invalid email address format');
                } else {
                    markValid(this);
                }
            });
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Password is required');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Form submission validation
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (emailInput && (emailInput.value.trim() === '' || !isValidEmail(emailInput.value))) {
                showError(emailInput, 'Valid email address is required');
                isValid = false;
            }
            
            if (passwordInput && passwordInput.value.trim() === '') {
                showError(passwordInput, 'Password is required');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

// ============================
// Registration form validation
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form[action="/register"]');
    
    if (registerForm) {
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirm');
        
        // Input event listeners
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Username is required');
                } else if (this.value.length < 3) {
                    showError(this, 'Username must be at least 3 characters long');
                } else if (this.value.length > 50) {
                    showError(this, 'Username cannot be longer than 50 characters');
                } else {
                    markValid(this);
                }
            });
        }
        
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Az e-mail cím megadása kötelező');
                } else if (!isValidEmail(this.value)) {
                    showError(this, 'Érvénytelen e-mail cím formátum');
                } else {
                    markValid(this);
                }
            });
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Password is required');
                } else if (this.value.length < 6) {
                    showError(this, 'Password must be at least 6 characters long');
                } else if (!isStrongPassword(this.value)) {
                    showError(this, 'Password must contain at least one letter and one number');
                } else {
                    markValid(this);
                }
                
                // Check password confirmation as well, if it already has a value
                if (passwordConfirmInput && passwordConfirmInput.value) {
                    if (this.value !== passwordConfirmInput.value) {
                        showError(passwordConfirmInput, 'The two passwords do not match');
                    } else {
                        markValid(passwordConfirmInput);
                    }
                }
            });
        }
        
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Password confirmation is required');
                } else if (passwordInput && this.value !== passwordInput.value) {
                    showError(this, 'The two passwords do not match');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Form submission validation
        registerForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (usernameInput) {
                if (usernameInput.value.trim() === '') {
                    showError(usernameInput, 'Username is required');
                    isValid = false;
                } else if (usernameInput.value.length < 3) {
                    showError(usernameInput, 'Username must be at least 3 characters long');
                    isValid = false;
                } else if (usernameInput.value.length > 50) {
                    showError(usernameInput, 'Username cannot be longer than 50 characters');
                    isValid = false;
                }
            }
            
            if (emailInput && (emailInput.value.trim() === '' || !isValidEmail(emailInput.value))) {
                showError(emailInput, 'Valid email address is required');
                isValid = false;
            }
            
            if (passwordInput) {
                if (passwordInput.value.trim() === '') {
                    showError(passwordInput, 'Password is required');
                    isValid = false;
                } else if (passwordInput.value.length < 6) {
                    showError(passwordInput, 'Password must be at least 6 characters long');
                    isValid = false;
                } else if (!isStrongPassword(passwordInput.value)) {
                    showError(passwordInput, 'Password must contain at least one letter and one number');
                    isValid = false;
                }
            }
            
            if (passwordConfirmInput) {
                if (passwordConfirmInput.value.trim() === '') {
                    showError(passwordConfirmInput, 'Password confirmation is required');
                    isValid = false;
                } else if (passwordInput && passwordConfirmInput.value !== passwordInput.value) {
                    showError(passwordConfirmInput, 'The two passwords do not match');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

// ============================
// Password reset form validation
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.querySelector('form[action="/reset"]');
    
    if (resetForm) {
        const emailInput = document.getElementById('email');
        
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Az e-mail cím megadása kötelező');
                } else if (!isValidEmail(this.value)) {
                    showError(this, 'Érvénytelen e-mail cím formátum');
                } else {
                    markValid(this);
                }
            });
        }
        
        resetForm.addEventListener('submit', function(event) {
            if (emailInput && (emailInput.value.trim() === '' || !isValidEmail(emailInput.value))) {
                showError(emailInput, 'Valid email address is required');
                event.preventDefault();
            }
        });
    }
});

// ============================
// Recipe submission form validation
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const recipeForm = document.querySelector('form[action="/recipe/submit"]');
    
    if (recipeForm) {
        const recipeNameInput = document.getElementById('recipeName');
        const categorySelect = document.getElementById('recipeCategory');
        const imageInput = document.getElementById('recipeImage');
        const prepTimeInput = document.getElementById('prepTime');
        const cookTimeInput = document.getElementById('cookTime');
        const servingsInput = document.getElementById('servings');
        const descriptionTextarea = document.getElementById('recipeDescription');
        const instructionsTextarea = document.getElementById('recipeInstructions');
        
        // Name validation
        if (recipeNameInput) {
            recipeNameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Recipe name is required');
                } else if (this.value.length < 3) {
                    showError(this, 'Recipe name must be at least 3 characters');
                } else if (this.value.length > 255) {
                    showError(this, 'Recipe name is too long (max. 255 characters)');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Catergory validation
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                if (this.value === '') {
                    showError(this, 'Kategória választása kötelező');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Image validation
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const acceptedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    const maxSize = 5 * 1024 * 1024; // 5 MB
                    
                    if (!acceptedFormats.includes(file.type)) {
                        showError(this, 'Csak kép formátumok támogatottak (JPG, PNG, GIF, WEBP)');
                    } else if (file.size > maxSize) {
                        showError(this, 'A kép mérete nem lehet nagyobb 5 MB-nál');
                    } else {
                        markValid(this);
                    }
                } else {
                    // If no image is selected, that's also fine
                    removeError(this);
                }
            });
        }
        
        // Time and quantity validation
        [prepTimeInput, cookTimeInput, servingsInput].forEach(input => {
            if (input) {
                input.addEventListener('input', function() {
                    if (this.value && (this.value <= 0 || !Number.isInteger(Number(this.value)))) {
                        showError(this, 'Csak pozitív egész szám adható meg');
                    } else {
                        removeError(this);
                    }
                });
            }
        });
        
        // Description validation
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A recept rövid leírása kötelező');
                } else if (this.value.length > 200) {
                    showError(this, 'A leírás túl hosszú (max. 200 karakter)');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Instruction validation
        if (instructionsTextarea) {
            instructionsTextarea.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'Az elkészítési útmutató megadása kötelező');
                } else if (this.value.length < 30) {
                    showError(this, 'Az útmutató túl rövid, kérjük adj részletesebb leírást');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Ingredient validation
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('ingredient-name')) {
                if (e.target.value.trim() === '') {
                    showError(e.target, 'A hozzávaló megadása kötelező');
                } else {
                    markValid(e.target);
                }
            }
            
            if (e.target.classList.contains('quantity-input')) {
                if (e.target.value.trim() === '') {
                    showError(e.target, 'A mennyiség megadása kötelező');
                } else if (parseFloat(e.target.value) <= 0) {
                    showError(e.target, 'A mennyiségnek pozitívnak kell lennie');
                } else {
                    markValid(e.target);
                }
            }
        });
        
        // Form validation
        recipeForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Name validation
            if (recipeNameInput) {
                if (recipeNameInput.value.trim() === '') {
                    showError(recipeNameInput, 'A recept nevének megadása kötelező');
                    isValid = false;
                } else if (recipeNameInput.value.length < 3) {
                    showError(recipeNameInput, 'A recept neve legalább 3 karakter legyen');
                    isValid = false;
                }
            }
            
            // Category check
            if (categorySelect && categorySelect.value === '') {
                showError(categorySelect, 'Kategória választása kötelező');
                isValid = false;
            }
            
            // Image check (if uploaded)
            if (imageInput && imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const acceptedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                const maxSize = 5 * 1024 * 1024; // 5 MB
                
                if (!acceptedFormats.includes(file.type)) {
                    showError(imageInput, 'Csak kép formátumok támogatottak (JPG, PNG, GIF, WEBP)');
                    isValid = false;
                } else if (file.size > maxSize) {
                    showError(imageInput, 'A kép mérete nem lehet nagyobb 5 MB-nál');
                    isValid = false;
                }
            }
            
            // Description validation
            if (descriptionTextarea) {
                if (descriptionTextarea.value.trim() === '') {
                    showError(descriptionTextarea, 'A recept rövid leírása kötelező');
                    isValid = false;
                } else if (descriptionTextarea.value.length > 200) {
                    showError(descriptionTextarea, 'A leírás túl hosszú (max. 200 karakter)');
                    isValid = false;
                }
            }
            
            // Instructions validation
            if (instructionsTextarea) {
                if (instructionsTextarea.value.trim() === '') {
                    showError(instructionsTextarea, 'Az elkészítési útmutató megadása kötelező');
                    isValid = false;
                } else if (instructionsTextarea.value.length < 30) {
                    showError(instructionsTextarea, 'Az útmutató túl rövid, kérjük adj részletesebb leírást');
                    isValid = false;
                }
            }
            
            // Igredients check
            const ingredientNameInputs = document.querySelectorAll('.ingredient-name');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            if (ingredientNameInputs.length === 0) {
                // If there are no ingredients, we must show an error message
                const ingredientsContainer = document.getElementById('ingredientsContainer');
                if (ingredientsContainer) {
                    if (!ingredientsContainer.querySelector('.invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block';
                        errorDiv.innerHTML = 'Legalább egy hozzávalót adj meg';
                        ingredientsContainer.appendChild(errorDiv);
                    }
                    isValid = false;
                }
            } else {
                // Validate all ingredients
                ingredientNameInputs.forEach((input, index) => {
                    if (input.value.trim() === '') {
                        showError(input, 'A hozzávaló neve kötelező');
                        isValid = false;
                    }
                });
                
                quantityInputs.forEach((input, index) => {
                    if (input.value.trim() === '') {
                        showError(input, 'A mennyiség megadása kötelező');
                        isValid = false;
                    } else if (parseFloat(input.value) <= 0) {
                        showError(input, 'A mennyiségnek pozitívnak kell lennie');
                        isValid = false;
                    }
                });
            }
            
            if (!isValid) {
                event.preventDefault();
                
                // Scroll to first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});

// ============================
// Frigde form validation
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const fridgeForm = document.getElementById('addItemForm');
    
    if (fridgeForm) {
        const itemNameInput = document.getElementById('itemName');
        const quantityInput = document.getElementById('itemQuantity');
        
        // Ingredient validation
        if (itemNameInput) {
            itemNameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A tétel nevének megadása kötelező');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Quantity validation
        if (quantityInput) {
            quantityInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A mennyiség megadása kötelező');
                } else if (parseFloat(this.value) <= 0) {
                    showError(this, 'A mennyiségnek pozitívnak kell lennie');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Form submit validation
        fridgeForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (itemNameInput && itemNameInput.value.trim() === '') {
                showError(itemNameInput, 'A tétel nevének megadása kötelező');
                isValid = false;
            }
            
            if (quantityInput) {
                if (quantityInput.value.trim() === '') {
                    showError(quantityInput, 'A mennyiség megadása kötelező');
                    isValid = false;
                } else if (parseFloat(quantityInput.value) <= 0) {
                    showError(quantityInput, 'A mennyiségnek pozitívnak kell lennie');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

// Validate forms when page is loaded
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.innerHTML = `
        .invalid-feedback {
            display: block;
            color: #dc3545;
            margin-top: 0.25rem;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .is-valid {
            border-color: #198754 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    `;
    document.head.appendChild(style);
});
