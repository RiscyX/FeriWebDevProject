<?php

// src/View/partials/carousel.php

// Ha dinamikusan töltenéd a slide-okat, ide betöltheted egy tömbből:
// $slides = [
//   ['img'=>'/assets/slide1.jpg','title'=>'Slide 1','caption'=>'First caption'],
//   …
// ];
// Most statikus példaként:

?>
<div id="homeCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button
                type="button"
                data-bs-target="#homeCarousel"
                data-bs-slide-to="0"
                class="active"
                aria-current="true">
        </button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="./assets/slide1.png" class="d-block w-100" alt="Slide 1">
        </div>
        <div class="carousel-item">
            <img src="./assets/slide2.png" class="d-block w-100" alt="Slide 2">
        </div>
        <div class="carousel-item">
            <img src="./assets/slide3.png" class="d-block w-100" alt="Slide 3">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
