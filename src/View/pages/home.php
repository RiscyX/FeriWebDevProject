<?php

/* Itt bármi lehet: carousel, üdvözlő szöveg, stb.
 * Ha dinamikus adat kell, a controller passzolja át változóban. */

require __DIR__ . '/../partials/carousel.php';

/* Ha több HTML-t generálsz, az egészet bufferbe gyűjtjük: */
$content = ob_get_clean();   // << lezárjuk a bufferelést
/* A $title-t is beállíthatod itt, de a controllerben is lehet */
