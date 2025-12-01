document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reservationForm');
    if (!form) return;
    const phone = '23754404526';

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        // Compose le message WhatsApp
        const nom = formData.get('nom') || '';
        const email = formData.get('email') || '';
        const dateNaissance = formData.get('date_naissance') || '';
        const dateLocation = formData.get('date_location') || '';
        const message = formData.get('message') || '';

        const textLines = [
            "Nouvelle réservation depuis Mano's Cluttery :",
            `Nom: ${nom}`,
            `Email: ${email}`,
            `Date de naissance: ${dateNaissance}`,
            `Date de location: ${dateLocation}`,
            `Message: ${message}`
        ];
        const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(textLines.join('\n'))}`;

        // Envoi AJAX vers traiter_reservation.php (ne quitte pas la page)
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            if (!res.ok) {
                console.warn('Échec POST vers traiter_reservation.php', res.status);
            }
        } catch (err) {
            console.warn('Erreur fetch vers traiter_reservation.php', err);
        }

        // Ouvre WhatsApp dans un nouvel onglet
        window.open(waUrl, '_blank');

        form.reset();
    });
});