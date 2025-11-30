<?php
// --- DEBUG (remove in production) ---
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Traiter seulement POST puis quitter (GET montrera le formulaire HTML plus bas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des champs
    $nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $date_naissance = trim(filter_input(INPUT_POST, 'date_naissance', FILTER_SANITIZE_STRING));
    $date_location = trim(filter_input(INPUT_POST, 'date_location', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    // Validation basique
    $errors = [];
    if ($nom === '') $errors[] = 'Nom requis.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if ($date_location === '') $errors[] = 'Date de location requise.';
    if ($message === '') $errors[] = 'Message requis.';

    if (!empty($errors)) {
        // réponse JSON utile pour fetch/ajax ; pour formulaire HTML, on pourra rediriger
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    // Empêcher l'injection d'en-têtes
    $cleanName = str_replace(["\r", "\n"], [' ', ' '], $nom);
    $cleanEmail = str_replace(["\r", "\n"], '', $email);

    // Préparer le mail
    $to = 'eejdimitri@gmail.com'; // destinataire — adapte si besoin
    $subject = "Nouvelle réservation de " . $cleanName;
    $body = "Nouvelle réservation reçue :\n\n";
    $body .= "Nom: " . $cleanName . "\n";
    $body .= "Email: " . $cleanEmail . "\n";
    $body .= "Date de naissance: " . $date_naissance . "\n";
    $body .= "Date de location: " . $date_location . "\n\n";
    $body .= "Message:\n" . $message . "\n";

    // En-têtes (From = adresse du site / Reply-To = client)
    $fromAddress = 'no-reply@ton-domaine.example'; // change selon ton domaine / configuration
    $headers = "From: Mano's Cluttery <{$fromAddress}>\r\n";
    $headers .= "Reply-To: {$cleanEmail}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Envoi
    $sent = mail($to, $subject, $body, $headers);

    if ($sent) {
        // Si le formulaire vient d'un submit classique, rediriger avec paramètre de succès
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?success=1');
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'message' => 'Réservation envoyée avec succès.']);
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du mail.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>page du catalogue</title>
    <link rel="stylesheet" href="style-apropos.css">
    <!-- Lien vers Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
   <header>
    <h1>MANO'S CLUTERY</h1>
    <nav>
        <ul>
            <li>
               <a href="ma_page.html">Acceuil</a> 
            </li>
            <li>
                <a href="#contact">contact</a>
            </li>
        </ul>
    </nav>
    <button>apprendre plus</button>
   </header>
    <main>
        <section id="hero">
           <h3>MERCI POUR VOTRE CONFIANCE</h3> 
                <p id="pb1">Bienvenue chez <strong>mano's Cluttery</strong>, chaque détail compte. C'est pourquoi nous vous proposons une sélection raffinée de couverts pensés pour sublimer vos tables et s'adapter à tous les styles d'événements du plus intime au plus prestigieux.Parcourez notre collection de fourchettes, couteaux, cuillères et accessoires de table soigneusement choisis pour leur esthétique, leur qualité et leur polyvalence. Que vous recherchiez une ambiance chic, moderne, rustique ou classique, vous trouverez ici les éléments parfaits pour compléter votre décor.</p>
                 </div>
            <div>
            <img  src="2.png" width="120px"/>
            <img  src="3.png" width="120px"/>
            <img  src="4.png" width="120px"/>
            <img  src="5.png" width="120px"/>
            <img  src="2.png" width="120px"/>
            </div>
        </section>
        <section id="album">  
             <div class="gauche">
                <div>                    
                    <!-- Icône de couverts -->
                     <h2>catalogue <i class="fas fa-utensils fa-sm"></i></h2>                    
                </div>
            </div>
            <div class="photo">
            <img id="i1" src="picture/2.png" width="120px"/>
            <img  id="i2" src="picture/Logo maman.png" width="120px"/>
            <img  id="i3" src="picture/4.png" width="120px"/>
            <img   id="i4" src="picture/3.png" width="120px"/>
            <img  id="i5" src="picture/2.png" width="120px"/>
            <img   id="i6" src="picture/Logo maman.png" width="120px"/>
            </div>
        </section>
        <section id="contact">
            <h3>VEILLEZ REMPLIR LE FORMULAIRE SUIVANT POUR FAIRE UNE RESERVATION</h3>
            
            <?php if (isset($message_succes)): ?>
                <div style="color: green; font-weight: bold; margin-bottom: 20px;">
                    <?php echo $message_succes; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($message_erreur)): ?>
                <div style="color: red; font-weight: bold; margin-bottom: 20px;">
                    <?php echo $message_erreur; ?>
                </div>
            <?php endif; ?>
            
            <!--formulaire-->
            <form method="POST"> 
                <input type="text" name="nom" placeholder="Votre nom" required>  
                <input type="email" name="email" placeholder="Votre adresse mail" required> 
                <input type="date" name="date_naissance" placeholder="Votre date de naissance" required>
                <input type="date" name="date_location" placeholder="Votre date de location" required>
                <textarea name="message" placeholder="Veillez remplir les informations nécessaires" required></textarea>     
                <button type="submit">Valider</button>  
            </form>

            <h3>NOUS VOUS REMERCIONS</h3>
        </section>
        
        <section id="reseaux">
        <div>
                       <a href="https://www.facebook.com/" target="_blank">
            <i class="fab fa-facebook fa-2x"></i>
            </a> 
        </div>
        <div class="carte">
            <a href="https://www.instagram.com/" target="_blank">
            <i class="fab fa-instagram fa-2x"></i>
            </a>
        </div>
        <div>
            <a href="https://www.youtube.com/" target="_blank">
            <i class="fab fa-youtube fa-2x"></i>
            </a>
        </div>
        </section>

        <footer>JED53 tout droits réservés 2025</footer>
   </main> 
</body>
</html>
