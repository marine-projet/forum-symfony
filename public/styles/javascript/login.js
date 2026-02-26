document.addEventListener('DOMContentLoaded', () => {
    console.log("JavaScript chargé !");
    
    const passwordInput = document.getElementById('registration_form_plainPassword');
    const togglePasswordButtons = document.querySelectorAll("#eye, #image_mdp");

    if (passwordInput && togglePasswordButtons) {
        // Boucle sur chaque élément de la liste
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function () {
                const isPasswordVisible = passwordInput.type === 'password';
                passwordInput.type = isPasswordVisible ? 'text' : 'password';
                console.log(`Bouton ${button.id} cliqué. Visibilité changée : ${passwordInput.type}`);
            });
        });
    } else {
           console.log(document.querySelector('#registration_form_plainPassword'));
   console.log(document.getElementById('image_mdp'));
    }
});