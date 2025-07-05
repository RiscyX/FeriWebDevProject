/**
 * FeriWebDevProject - User functions and utilities
 * 
 * Általános funkciók a weboldal működéséhez
 */

document.addEventListener('DOMContentLoaded', function() {
    // Csak akkor futtassuk az ellenőrzést, ha a felhasználó be van jelentkezve
    if (document.body.classList.contains('user-logged-in')) {
        // Kezdeti ellenőrzés
        checkUserStatus();
        
        // Rendszeres ellenőrzés (30 másodpercenként)
        setInterval(checkUserStatus, 30000);
    }

    // Fridge - Új elem hozzáadása funkció
    setupFridgeItemForm();
});

/**
 * Felhasználói státusz ellenőrzése
 * Ha a felhasználó bannolva lett, kijelentkezteti és átirányítja
 */
function checkUserStatus() {
    fetch('/api/user/status')
        .then(response => {
            if (!response.ok) {
                throw new Error('Hálózati hiba történt');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.is_banned) {
                // Felhasználó bannolva lett, kijelentkeztetés
                window.location.href = '/login?banned=1';
            }
        })
        .catch(error => {
            console.error('Hiba a felhasználói státusz ellenőrzésekor:', error);
        });
}

/**
 * Hűtőszekrény - form kezelése
 * Ellenőrzi, hogy az oldal tartalmaz-e hűtőszekrény kezelő űrlapot, és ha igen,
 * meghívja a fridgeFunctions.js-ben található inicializáló függvényt
 */
function setupFridgeItemForm() {
    // Ellenőrizzük, hogy a fridge oldalon vagyunk-e
    if (document.getElementById('addItemForm')) {
        // Ellenőrizzük, hogy a fridgeFunctions.js betöltődött-e
        if (typeof initFridgeModal === 'undefined') {
            // Ha a fridgeFunctions.js még nem töltődött be, akkor importáljuk dinamikusan
            import('./fridgeFunctions.js')
                .then(module => {
                    // A modul betöltődött, de nem szükséges meghívni az initFridgeModal-t,
                    // mivel a fridgeFunctions.js automatikusan inicializálódik a betöltődéskor
                    console.log('fridgeFunctions.js sikeresen betöltődött');
                })
                .catch(error => {
                    console.error('Hiba a fridgeFunctions.js betöltésekor:', error);
                });
        }
    }
}
