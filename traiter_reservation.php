<?php
/**
 * traiter_reservation.php — Mano's Clutlery
 * Traitement sécurisé des réservations
 */

// 1. DÉMARRAGE SESSION SÉCURISÉE
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,   // HTTPS uniquement en production
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

// 2. HEADERS DE SÉCURITÉ
header('Content-Security-Policy: default-src \'self\'');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// 3. VÉRIFICATION MÉTHODE HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: apropos.html?error=method');
    exit;
}

// 4. PROTECTION CSRF
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Requête invalide. Token CSRF manquant ou incorrect.');
}
// Régénérer le token après usage
unset($_SESSION['csrf_token']);

// 5. RATE LIMITING (max 5 soumissions / 10 min par IP)
$ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = 'reservation_' . md5($ip);
if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = ['count' => 0, 'first' => time()];
}
if (time() - $_SESSION[$key]['first'] > 600) {
    $_SESSION[$key] = ['count' => 0, 'first' => time()];
}
if ($_SESSION[$key]['count'] >= 5) {
    http_response_code(429);
    die('Trop de soumissions. Veuillez réessayer dans 10 minutes.');
}
$_SESSION[$key]['count']++;

// 6. COLLECTE & ASSAINISSEMENT DES DONNÉES
$nom          = trim(strip_tags($_POST['nom']          ?? ''));
$email        = trim(strip_tags($_POST['email']        ?? ''));
$date_location = trim(strip_tags($_POST['date_location'] ?? ''));
$type_event   = trim(strip_tags($_POST['type_event']   ?? ''));
$message      = trim(strip_tags($_POST['message']      ?? ''));

// 7. VALIDATION SERVEUR
$errors = [];

// Nom
if (empty($nom) || mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
    $errors[] = 'Nom invalide (2–100 caractères requis).';
}
if (!preg_match('/^[\p{L}\s\-\'\.]+$/u', $nom)) {
    $errors[] = 'Le nom contient des caractères non autorisés.';
}

// Email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Adresse e-mail invalide.';
}

// Date de location — doit être future
if (empty($date_location)) {
    $errors[] = 'La date de location est requise.';
} else {
    $d = DateTime::createFromFormat('Y-m-d', $date_location);
    if (!$d || $d->format('Y-m-d') !== $date_location) {
        $errors[] = 'Format de date invalide.';
    } elseif ($d < new DateTime('today')) {
        $errors[] = 'La date de location doit être dans le futur.';
    }
}

// Type événement (optionnel mais whitelist)
$types_autorises = ['', 'mariage', 'anniversaire', 'reception', 'diner', 'autre'];
if (!in_array($type_event, $types_autorises, true)) {
    $type_event = '';
}

// Message
if (empty($message) || mb_strlen($message) < 20 || mb_strlen($message) > 1000) {
    $errors[] = 'Le message doit contenir entre 20 et 1000 caractères.';
}

// 8. DÉTECTION SPAM (honeypot — champ invisible à ne pas remplir)
if (!empty($_POST['website'])) {
    // Bot détecté — on simule le succès sans envoyer
    header('Location: apropos.html?success=1');
    exit;
}

// 9. TRAITEMENT ERREURS
if (!empty($errors)) {
    $err_param = urlencode(implode('|', $errors));
    header('Location: apropos.html?error=' . $err_param);
    exit;
}

// 10. PRÉPARATION EMAIL
$destinataire = 'eejdimitri@gmail.com';
$sujet        = '[Mano\'s Clutlery] Réservation de ' . htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');

$type_label = match($type_event) {
    'mariage'     => 'Mariage',
    'anniversaire'=> 'Anniversaire',
    'reception'   => 'Réception d\'entreprise',
    'diner'       => 'Dîner privé',
    'autre'       => 'Autre',
    default       => 'Non précisé',
};

$contenu  = "=== NOUVELLE RÉSERVATION — Mano's Clutlery ===\n\n";
$contenu .= "Date de réception : " . date('d/m/Y à H:i') . "\n";
$contenu .= "IP expéditeur     : " . $ip . "\n\n";
$contenu .= "--- Coordonnées ---\n";
$contenu .= "Nom       : " . $nom . "\n";
$contenu .= "Email     : " . $email . "\n\n";
$contenu .= "--- Détails ---\n";
$contenu .= "Date souhaitée : " . date('d/m/Y', strtotime($date_location)) . "\n";
$contenu .= "Type événement : " . $type_label . "\n\n";
$contenu .= "--- Message ---\n" . $message . "\n";

$headers  = "From: noreply@manos-clutlery.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: Manos-Clutlery-Mailer/1.0\r\n";

// 11. ENVOI (avec log en cas d'échec)
$envoye = mail($destinataire, $sujet, $contenu, $headers);

if (!$envoye) {
    error_log('[Mano Clutlery] Échec envoi mail pour ' . $email . ' — ' . date('c'));
    header('Location: apropos.html?error=mail');
    exit;
}

// 12. REDIRECTION SUCCÈS
header('Location: apropos.html?success=1');
exit;
?>
