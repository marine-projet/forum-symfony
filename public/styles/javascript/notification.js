document.addEventListener('DOMContentLoaded', () => {
    // Obtenez tous les liens de notifications
    const notificationLinks = document.querySelectorAll('.notification-link');

    notificationLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Empêcher la navigation avant la mise à jour

            // Récupérez l'ID de la notification à partir de l'attribut `data-notification-id`
            const notificationId = this.getAttribute('data-notification-id');
            const targetUrl = this.getAttribute('href'); // URL de redirection après

            // Envoyez une requête POST à la route Symfony
            fetch(`/notifications/mark-as-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest', // Important pour Symfony
                },
            })
            .then(response => {
                // Vérifiez la réponse
                if (response.ok) {
                    console.log(`Notification ${notificationId} marquée comme lue.`);

                    // Optionnel : mettez à jour visuellement les notifications
                    const notificationElement = this.closest('li');
                    if (notificationElement) {
                        notificationElement.classList.remove('new-notification');
                        const badge = notificationElement.querySelector('.badge');
                        if (badge) badge.remove();
                    }
                } else {
                    console.error(`Erreur lors de la mise à jour de la notification ${notificationId}`);
                }

                // Redirigez l'utilisateur vers le lien du sujet
                window.location.href = targetUrl;
            })
            .catch(error => {
                console.error('Une erreur est survenue :', error);
                // Redirection en cas d'erreur
                window.location.href = targetUrl;
            });
        });
    });
});