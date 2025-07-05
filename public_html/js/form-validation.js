/**
 * Űrlapvalidációs segédfüggvények
 * FeriWebDevProject - 2025
 */

/**
 * A hibaüzenet megjelenítéséért felelős függvény
 * @param {HTMLElement} inputElement - Az input elem, amelyhez a hibaüzenetet kapcsoljuk
 * @param {string} message - A hibaüzenet szövege
 */
function showError(inputElement, message) {
    // Töröljük a korábbi hibaüzenetet, ha van
    removeError(inputElement);
    
    // Adjuk hozzá a hibaosztályt az input elemhez
    inputElement.classList.add('is-invalid');
    
    // Hozzuk létre a hibaüzenet elemet
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.innerHTML = message;
    
    // Helyezzük el az input után a hibaüzenetet
    if (inputElement.parentElement.classList.contains('input-group')) {
        // Input group esetén a szülő után helyezzük el
        inputElement.parentElement.parentElement.appendChild(errorDiv);
    } else {
        // Egyébként közvetlenül az input után
        inputElement.parentElement.appendChild(errorDiv);
    }
}

/**
 * Eltávolítja a hibaüzenetet egy input elemről
 * @param {HTMLElement} inputElement - Az input elem, amelyről a hibaüzenetet eltávolítjuk
 */
function removeError(inputElement) {
    inputElement.classList.remove('is-invalid');
    
    // Megkeressük és eltávolítjuk a kapcsolódó hibaüzenetet
    const parent = inputElement.parentElement;
    const grandparent = parent.parentElement;
    
    // Ellenőrizzük mindkét lehetséges helyet a hibaüzenethez
    [parent, grandparent].forEach(element => {
        const errorDiv = element.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    });
}

/**
 * Sikeres validáció esetén hozzáadja a sikeres osztályt
 * @param {HTMLElement} inputElement - Az input elem, amelyet sikeresnek jelölünk
 */
function markValid(inputElement) {
    removeError(inputElement);
    inputElement.classList.add('is-valid');
}

/**
 * E-mail cím formátum ellenőrzése
 * @param {string} email - Az ellenőrizendő e-mail cím
 * @returns {boolean} Érvényes-e az e-mail cím
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

/**
 * Jelszó erősség ellenőrzése
 * @param {string} password - Az ellenőrizendő jelszó
 * @returns {boolean} Elég erős-e a jelszó
 */
function isStrongPassword(password) {
    // Minimum 6 karakter, legalább egy szám és egy betű
    return password.length >= 6 && /[0-9]/.test(password) && /[a-zA-Z]/.test(password);
}

// ============================
// Bejelentkezési űrlap validáció
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action="/login"]');
    
    if (loginForm) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        // Input eseményfigyelők
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
                    showError(this, 'A jelszó megadása kötelező');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Form elküldés validálása
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (emailInput && (emailInput.value.trim() === '' || !isValidEmail(emailInput.value))) {
                showError(emailInput, 'Érvényes e-mail cím megadása kötelező');
                isValid = false;
            }
            
            if (passwordInput && passwordInput.value.trim() === '') {
                showError(passwordInput, 'A jelszó megadása kötelező');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

// ============================
// Regisztrációs űrlap validáció
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form[action="/register"]');
    
    if (registerForm) {
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirm');
        
        // Input eseményfigyelők
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A felhasználónév megadása kötelező');
                } else if (this.value.length < 3) {
                    showError(this, 'A felhasználónév legalább 3 karakter hosszú legyen');
                } else if (this.value.length > 50) {
                    showError(this, 'A felhasználónév legfeljebb 50 karakter lehet');
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
                    showError(this, 'A jelszó megadása kötelező');
                } else if (this.value.length < 6) {
                    showError(this, 'A jelszónak legalább 6 karakter hosszúnak kell lennie');
                } else if (!isStrongPassword(this.value)) {
                    showError(this, 'A jelszónak tartalmaznia kell legalább egy betűt és egy számot');
                } else {
                    markValid(this);
                }
                
                // Ellenőrizzük a jelszó megerősítést is, ha már van benne érték
                if (passwordConfirmInput && passwordConfirmInput.value) {
                    if (this.value !== passwordConfirmInput.value) {
                        showError(passwordConfirmInput, 'A két jelszó nem egyezik');
                    } else {
                        markValid(passwordConfirmInput);
                    }
                }
            });
        }
        
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A jelszó megerősítése kötelező');
                } else if (passwordInput && this.value !== passwordInput.value) {
                    showError(this, 'A két jelszó nem egyezik');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Form elküldés validálása
        registerForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (usernameInput) {
                if (usernameInput.value.trim() === '') {
                    showError(usernameInput, 'A felhasználónév megadása kötelező');
                    isValid = false;
                } else if (usernameInput.value.length < 3) {
                    showError(usernameInput, 'A felhasználónév legalább 3 karakter hosszú legyen');
                    isValid = false;
                } else if (usernameInput.value.length > 50) {
                    showError(usernameInput, 'A felhasználónév legfeljebb 50 karakter lehet');
                    isValid = false;
                }
            }
            
            if (emailInput && (emailInput.value.trim() === '' || !isValidEmail(emailInput.value))) {
                showError(emailInput, 'Érvényes e-mail cím megadása kötelező');
                isValid = false;
            }
            
            if (passwordInput) {
                if (passwordInput.value.trim() === '') {
                    showError(passwordInput, 'A jelszó megadása kötelező');
                    isValid = false;
                } else if (passwordInput.value.length < 6) {
                    showError(passwordInput, 'A jelszónak legalább 6 karakter hosszúnak kell lennie');
                    isValid = false;
                } else if (!isStrongPassword(passwordInput.value)) {
                    showError(passwordInput, 'A jelszónak tartalmaznia kell legalább egy betűt és egy számot');
                    isValid = false;
                }
            }
            
            if (passwordConfirmInput) {
                if (passwordConfirmInput.value.trim() === '') {
                    showError(passwordConfirmInput, 'A jelszó megerősítése kötelező');
                    isValid = false;
                } else if (passwordInput && passwordConfirmInput.value !== passwordInput.value) {
                    showError(passwordConfirmInput, 'A két jelszó nem egyezik');
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
// Jelszó-visszaállítás űrlap validáció
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
                showError(emailInput, 'Érvényes e-mail cím megadása kötelező');
                event.preventDefault();
            }
        });
    }
});

// ============================
// Recept beküldési űrlap validáció
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
        
        // Név validálás
        if (recipeNameInput) {
            recipeNameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A recept nevének megadása kötelező');
                } else if (this.value.length < 3) {
                    showError(this, 'A recept neve legalább 3 karakter legyen');
                } else if (this.value.length > 255) {
                    showError(this, 'A recept neve túl hosszú (max. 255 karakter)');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Kategória validálás
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                if (this.value === '') {
                    showError(this, 'Kategória választása kötelező');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Kép validálás
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
                    // Ha nincs kiválasztva kép, az is rendben van
                    removeError(this);
                }
            });
        }
        
        // Idő és adag validálás
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
        
        // Leírás validálás
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
        
        // Elkészítési útmutató validálás
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
        
        // Hozzávalók validálás - dinamikusan generált elemek
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
        
        // Form elküldés validálása
        recipeForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Név ellenőrzés
            if (recipeNameInput) {
                if (recipeNameInput.value.trim() === '') {
                    showError(recipeNameInput, 'A recept nevének megadása kötelező');
                    isValid = false;
                } else if (recipeNameInput.value.length < 3) {
                    showError(recipeNameInput, 'A recept neve legalább 3 karakter legyen');
                    isValid = false;
                }
            }
            
            // Kategória ellenőrzés
            if (categorySelect && categorySelect.value === '') {
                showError(categorySelect, 'Kategória választása kötelező');
                isValid = false;
            }
            
            // Kép ellenőrzés (ha van fájl kiválasztva)
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
            
            // Leírás ellenőrzés
            if (descriptionTextarea) {
                if (descriptionTextarea.value.trim() === '') {
                    showError(descriptionTextarea, 'A recept rövid leírása kötelező');
                    isValid = false;
                } else if (descriptionTextarea.value.length > 200) {
                    showError(descriptionTextarea, 'A leírás túl hosszú (max. 200 karakter)');
                    isValid = false;
                }
            }
            
            // Útmutató ellenőrzés
            if (instructionsTextarea) {
                if (instructionsTextarea.value.trim() === '') {
                    showError(instructionsTextarea, 'Az elkészítési útmutató megadása kötelező');
                    isValid = false;
                } else if (instructionsTextarea.value.length < 30) {
                    showError(instructionsTextarea, 'Az útmutató túl rövid, kérjük adj részletesebb leírást');
                    isValid = false;
                }
            }
            
            // Hozzávalók ellenőrzése
            const ingredientNameInputs = document.querySelectorAll('.ingredient-name');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            if (ingredientNameInputs.length === 0) {
                // Ha nincs hozzávaló, hibaüzenetet kell mutatnunk
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
                // Ellenőrizzük az összes hozzávalót
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
                
                // Görgetés az első hibához
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});

// ============================
// Hűtőszekrény űrlap validáció
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const fridgeForm = document.getElementById('addItemForm');
    
    if (fridgeForm) {
        const itemNameInput = document.getElementById('itemName');
        const quantityInput = document.getElementById('itemQuantity');
        
        // Tétel neve validálás
        if (itemNameInput) {
            itemNameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    showError(this, 'A tétel nevének megadása kötelező');
                } else {
                    markValid(this);
                }
            });
        }
        
        // Mennyiség validálás
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
        
        // Form elküldés validálása
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

// Validáljuk az űrlapokat amikor betöltődik az oldal
document.addEventListener('DOMContentLoaded', function() {
    // CSS hozzáadása a hibaüzenetekhez
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
