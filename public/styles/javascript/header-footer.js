const icon = document.getElementById('icon'); 
const nav = document.getElementById('nav'); 
const links = document.querySelectorAll('nav ul li a');

icon.addEventListener('click', function () {
    nav.classList.toggle('active'); 
});

links.forEach((link) => {
    link.addEventListener('click', function (event) {
        event.preventDefault(); 

        nav.classList.remove('active');

        const href = this.getAttribute('href'); 
        setTimeout(() => {
            window.location.href = href; 
        }, 500); 
    });
});

// Obtenez l'URL actuelle
const currentPath = window.location.pathname;

// Parcourez tous les liens de navigation
document.querySelectorAll('nav li a').forEach(item => {
    const href = item.getAttribute('href');

    // Vérifiez si le chemin actuel correspond exactement ou s'il s'agit d'une sous-route
    if (currentPath === href || (currentPath.startsWith(href) && href !== '/')) {
        // Ajoute la classe active uniquement pour le lien correspondant ou une sous-route
        item.classList.add('active');
    } else {
        // Supprime la classe active si le chemin ne correspond plus
        item.classList.remove('active');
    }
});

// Log (facultatif)
console.log('Script exécuté pour gérer les sous-routes.');