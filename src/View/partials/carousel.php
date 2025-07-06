<?php

?>
<div id="homeCarousel"
     class="carousel slide mb-5 shadow-lg
     rounded-4 overflow-hidden" data-bs-ride="carousel" style="background: var(--secondary-bg, #f5f5f5);">
    <div class="carousel-indicators">
        <button 
                type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0"
                class="active" aria-current="true">
        </button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="./assets/slide1.png" class="d-block w-100 carousel-img" alt="Slide 1">
            <div class="carousel-caption d-none d-md-block bg-opacity-75 rounded-3 p-3 shadow-lg">
                <h5 class="fw-bold">Add meg az alapanyagokat</h5>
                <p>Gépeld be a hozzávalókat, mi közben valós időben súgunk a pontos névhez és mértékegységhez.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="./assets/slide2.png" class="d-block w-100 carousel-img" alt="Slide 2">
            <div class="carousel-caption d-none d-md-block bg-opacity-75 rounded-3 p-3 shadow-lg">
                <h5 class="fw-bold">Személyre szabott receptötletek</h5>
                <p>Ha legfeljebb két hozzávaló hiányzik, jól láthatóan kiemeljük, mire lesz még szükséged.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="./assets/slide3.png" class="d-block w-100 carousel-img" alt="Slide 3">
            <div class="carousel-caption d-none d-md-block bg-opacity-75 rounded-3 p-3 shadow-lg">
                <h5 class="fw-bold">Készítsd el & mentsd el</h5>
                <p>Tedd kedvencnek vagy állítsd be egy hétre szóló menüd részeként.</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Előző</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Következő</span>
    </button>
</div>
<style>
.carousel-img {
    height: 60vh;
    object-fit: cover;
    filter: brightness(0.92) saturate(1.1);
    transition: filter 0.3s;
}
.carousel-item.active .carousel-img {
    filter: brightness(1) saturate(1.15);
}
.carousel-caption {
    background: rgba(33,33,33,0.65);
    color: #fff;
    text-shadow: 0 2px 8px rgba(0,0,0,0.25);
    border-radius: 1rem;
}
[data-bs-theme="dark"] .carousel-caption {
    background: rgba(30, 40, 50, 0.85);
    color: var(--primary-text);
}
.carousel-indicators [data-bs-target] {
    background-color: var(--navbar-text, #689f38);
    width: 14px;
    height: 14px;
    border-radius: 50%;
    margin: 0 6px;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.carousel-indicators .active {
    opacity: 1;
    background-color: var(--navbar-hover, #8bc34a);
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25));
}
</style>
