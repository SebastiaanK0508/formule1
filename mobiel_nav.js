document.addEventListener('DOMContentLoaded', () => {
    const mobileMenu = document.getElementById('mobile-menu');
    const toggleButtons = document.querySelectorAll('.menu-toggle');
    if (mobileMenu && toggleButtons.length > 0) {
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                mobileMenu.classList.toggle('active');
                if (mobileMenu.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            });
        });
        const menuLinks = mobileMenu.querySelectorAll('nav a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        });
    }
});