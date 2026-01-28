document.addEventListener('DOMContentLoaded', () => {
    // Haal de benodigde elementen op
    const mobileMenu = document.getElementById('mobile-menu');
    const toggleButtons = document.querySelectorAll('.menu-toggle');

    // Controleer of de elementen bestaan om fouten te voorkomen
    if (mobileMenu && toggleButtons.length > 0) {
        
        // Loop door alle knoppen met de class 'menu-toggle'
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Wissel de 'active' class op het menu
                mobileMenu.classList.toggle('active');

                // Optioneel: Blokkeer scrollen op de body wanneer menu open is
                if (mobileMenu.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Sluit menu als er op een link wordt geklikt
        const menuLinks = mobileMenu.querySelectorAll('nav a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        });
    }
});