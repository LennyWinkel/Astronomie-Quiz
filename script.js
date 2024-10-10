document.addEventListener('DOMContentLoaded', function () {
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const nav = document.querySelector('nav');

    hamburgerMenu.addEventListener('click', function () {
        this.classList.toggle('active');
        nav.classList.toggle('show');
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 800) {
            hamburgerMenu.classList.remove('active');
            nav.classList.remove('show');
        }
    });
});