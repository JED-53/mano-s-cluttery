# Mano's Clutlery — Site Web

Projet de site vitrine et de réservation pour **Mano's Clutlery**, service de location de couverts haut de gamme.

---

## 📁 Structure des fichiers

```
manos-clutlery/
├── index.html              # Page d'accueil
├── style-index.css         # CSS page d'accueil
├── apropos.html            # Catalogue + formulaire de réservation
├── style-apropos.css       # CSS page catalogue
├── login.html              # Page de connexion
├── style-login.css         # CSS page login
├── traiter_reservation.php # Backend sécurisé (PHP)
├── 2.png / 3.png / 4.png / 5.png  # Photos produits
└── C.png                   # Logo
```

---

## 🔒 Mesures de sécurité implémentées

### Côté serveur (PHP)

| Mesure | Explication |
|--------|-------------|
| **Protection CSRF** | Token unique en session, vérifié avec `hash_equals()` (résistant aux timing attacks) |
| **Rate Limiting** | Max 5 soumissions par IP toutes les 10 minutes (stocké en session) |
| **Validation stricte** | Chaque champ est validé : longueur, format, regex, whitelist |
| **Assainissement** | `strip_tags()` + `trim()` sur tous les inputs |
| **Honeypot anti-spam** | Champ invisible dans le formulaire — si rempli, le bot est ignoré silencieusement |
| **Headers HTTP** | CSP, X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy |
| **Session sécurisée** | `httponly`, `samesite=Strict`, `use_strict_mode` |
| **Logging des erreurs** | Les échecs d'envoi sont loggés côté serveur sans exposer d'infos |
| **Date validation** | La date de location doit être dans le futur (vérification serveur) |

### Côté client (HTML/JS)

| Mesure | Explication |
|--------|-------------|
| **Validation HTML5** | Attributs `required`, `type`, `minlength`, `maxlength` |
| **Validation JS** | Vérification avant soumission (regex email, longueur, présence) |
| **Attributs autocomplete** | Aide le navigateur, réduit les risques de phishing |
| **`novalidate`** | La validation native HTML5 est désactivée au profit du JS pour de meilleurs messages |
| **`rel="noopener noreferrer"`** | Sur tous les liens `target="_blank"` (protection contre tabnapping) |
| **`loading="lazy"`** | Sur les images du catalogue |

---

## 🚀 Mise en production — checklist

- [ ] Activer HTTPS (certificat SSL/TLS obligatoire)
- [ ] Activer `cookie_secure => true` dans `session_start()`
- [ ] Configurer le serveur mail (SMTP via PHPMailer recommandé plutôt que `mail()`)
- [ ] Générer le token CSRF dans la session **avant** l'affichage du formulaire
- [ ] Ajouter un `.htaccess` pour protéger les fichiers PHP
- [ ] Mettre en place un système de log d'accès
- [ ] Configurer des backups automatiques

---

## 🎨 Identité visuelle

- **Couleur principale** : Turquoise `#48B6AC`
- **Couleur secondaire** : Rose poudré `#F4B8CB` / `#E8789A`
- **Fond** : Crème `#FDF6EF`
- **Typographie display** : Cormorant Garamond (serif élégant)
- **Typographie corps** : Jost (sans-serif moderne et lisible)

---

© 2025 Mano's Clutlery — Tous droits réservés
