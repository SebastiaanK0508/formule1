document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('main-nav-links');
    const toggleButton = document.querySelector('.menu-toggle');
        if (nav && toggleButton) {
        toggleButton.addEventListener('click', () => {
            const isVisible = nav.getAttribute('data-visible') === "true";
            if (isVisible) {
                nav.setAttribute('data-visible', 'false');
                toggleButton.setAttribute('aria-expanded', 'false');
            } else {
                nav.setAttribute('data-visible', 'true');
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        });
    }
});