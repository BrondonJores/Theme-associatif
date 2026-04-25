# Guide des bonnes pratiques de securite - Theme Associatif

Ce document decrit les pratiques de securite implementees dans le theme et les
regles a respecter lors de tout developpement sur le theme.

---

## Table des matieres

1. [Principes generaux](#principes-generaux)
2. [Sanitization des entrees](#sanitization-des-entrees)
3. [Echappement des sorties](#echappement-des-sorties)
4. [Validation des donnees](#validation-des-donnees)
5. [Protection CSRF et Nonces](#protection-csrf-et-nonces)
6. [Gestion des roles et permissions](#gestion-des-roles-et-permissions)
7. [Hashing et encryption](#hashing-et-encryption)
8. [Logging de securite](#logging-de-securite)
9. [Regles a ne jamais enfreindre](#regles-a-ne-jamais-enfreindre)

---

## Principes generaux

Le theme applique le modele de securite en couches :

```
[Entree utilisateur]
       |
  [Sanitization]  --> Nettoyer les donnees brutes
       |
  [Validation]    --> Verifier les regles metier
       |
  [Traitement]    --> Logique applicative
       |
  [Stockage]      --> Donnees propres en base
       |
  [Recuperation]
       |
  [Echappement]   --> Encoder selon le contexte de sortie
       |
[Affichage]
```

**Regle fondamentale** : Sanitiser a l'entree, echapper a la sortie.

---

## Sanitization des entrees

Le `SanitizerService` doit etre utilise sur TOUTES les donnees provenant de l'exterieur :
`$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE`, `$_SERVER`, donnees tierces via API.

### Utilisation

```php
$sanitizer = ta_security()->getSanitizer();

// Champ texte simple (input type="text")
$name = $sanitizer->sanitizeTextField($_POST['name'] ?? '');

// Champ textarea
$description = $sanitizer->sanitizeTextarea($_POST['description'] ?? '');

// Adresse email
$email = $sanitizer->sanitizeEmail($_POST['email'] ?? '');

// URL
$website = $sanitizer->sanitizeUrl($_POST['website'] ?? '');

// Entier (ID, quantite, etc.)
$userId = $sanitizer->sanitizeInt($_POST['user_id'] ?? 0);

// Tableau de donnees
$formData = $sanitizer->sanitizeArray($_POST, 'text');
```

### Choisir la bonne methode

| Type de champ          | Methode a utiliser       |
|------------------------|--------------------------|
| input text, search     | `sanitizeTextField()`    |
| textarea               | `sanitizeTextarea()`     |
| input email            | `sanitizeEmail()`        |
| input url              | `sanitizeUrl()`          |
| input number (entier)  | `sanitizeInt()`          |
| input number (decimal) | `sanitizeFloat()`        |
| editeur WYSIWYG        | `sanitizeHtml()`         |
| slug, cle technique    | `sanitizeKey()`          |
| classe CSS, ID HTML    | `sanitizeHtmlClass()`    |
| nom de fichier         | `sanitizeFileName()`     |

---

## Echappement des sorties

Le `EscaperService` doit etre utilise sur TOUTES les donnees affichees dans les templates,
meme les donnees provenant de la base de donnees ou de constantes.

### Contextes d'echappement

```php
$escaper = ta_security()->getEscaper();

// Contexte HTML : entre balises <p>, <span>, <div>, etc.
echo $escaper->escHtml($title);

// Contexte attribut HTML : value="...", alt="...", class="...", etc.
echo '<input value="' . $escaper->escAttr($value) . '">';

// Contexte URL : href="...", src="...", action="...", etc.
echo '<a href="' . $escaper->escUrl($url) . '">';

// Contexte JavaScript : dans <script> ou gestionnaires d'evenements
echo 'var name = "' . $escaper->escJs($name) . '";';

// Contexte CSS : dans <style> ou attribut style="..."
echo '<div style="color: ' . $escaper->escCss($color) . '">';

// HTML avec balises autorisees (contenu utilisateur enrichi)
echo $escaper->escHtmlKses($userContent);

// Traduction et echappement combines
echo $escaper->escHtmlTrans('Bienvenue', 'theme-associatif');
```

### Regles d'echappement

- Toujours echapper au moment de l'affichage, pas au stockage
- Ne jamais stocker des donnees deja echappees (double echappement)
- Choisir le bon contexte : une URL dans un attribut href utilise `escUrl()`, pas `escHtml()`
- Les fonctions `*E()` affichent directement : `escHtmlE($value)` equivalent a `echo escHtml($value)`

---

## Validation des donnees

Le `ValidatorService` verifie que les donnees respectent les regles metier
APRES sanitization et AVANT traitement.

### Regles disponibles

```php
$validator = ta_security()->getValidator();

$isValid = $validator->validate($_POST, [
    'name'     => ['required', 'max_length:100'],
    'email'    => ['required', 'email'],
    'website'  => ['url'],
    'age'      => ['integer'],
    'bio'      => ['max_length:500'],
    'password' => ['required', 'min_length:8'],
    'slug'     => ['regex:/^[a-z0-9-]+$/'],
]);

if ($validator->fails()) {
    $errors = $validator->getErrors();
    // Traitement des erreurs...
}
```

### Regles personnalisees

```php
$validator->addCustomRule(
    'french_phone',
    fn($value) => preg_match('/^(\+33|0)[1-9](\d{2}){4}$/', $value) === 1,
    'Le champ :field doit etre un numero de telephone francais valide.'
);

$validator->validate($_POST, [
    'phone' => ['french_phone'],
]);
```

### Liste des regles natives

| Regle             | Description                          | Exemple               |
|-------------------|--------------------------------------|-----------------------|
| `required`        | Champ obligatoire                    | `'required'`          |
| `email`           | Adresse email valide                 | `'email'`             |
| `url`             | URL valide (http/https)              | `'url'`               |
| `integer`         | Nombre entier valide                 | `'integer'`           |
| `min_length:N`    | Longueur minimale de N caracteres    | `'min_length:8'`      |
| `max_length:N`    | Longueur maximale de N caracteres    | `'max_length:255'`    |
| `regex:/pattern/` | Correspondance a une regex           | `'regex:/^[a-z]+$/'`  |

---

## Protection CSRF et Nonces

Tous les formulaires qui modifient des donnees DOIVENT inclure un token CSRF.

### Formulaires

```php
$csrf = ta_security()->getCsrfProtector();

// Dans le template du formulaire
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php echo wp_kses(
        $csrf->protectForm('member_registration'),
        ['input' => ['type' => true, 'id' => true, 'name' => true, 'value' => true]]
    ); ?>
    <!-- ou via le hook automatique : -->
    <?php do_action('ta_form_fields', 'member_registration'); ?>

    <input type="text" name="name">
    <button type="submit">S'inscrire</button>
</form>
<?php

// Dans le handler (traitement du formulaire)
$csrf->assertValid('member_registration');

// Si assertValid() ne leve pas d'exception, le token est valide
$sanitizer = ta_security()->getSanitizer();
$name = $sanitizer->sanitizeTextField($_POST['name'] ?? '');
```

### Nonces pour les actions AJAX

```php
$nonce = ta_security()->getNonceManager();

// Generer un nonce pour une action AJAX
$token = $nonce->create('load_members_list');

// Passer le nonce au JavaScript via wp_localize_script()
wp_localize_script('theme-main', 'taAjax', [
    'nonce' => $token,
    'url'   => admin_url('admin-ajax.php'),
]);

// Dans le handler AJAX
add_action('wp_ajax_load_members', function () use ($nonce): void {
    $nonce->check(
        sanitize_text_field(wp_unslash($_POST['nonce'] ?? '')),
        'load_members_list'
    );
    // Traitement securise...
});
```

---

## Gestion des roles et permissions

### Verifier les permissions avant toute action

```php
$permissions = ta_security()->getPermissionChecker();

// Verification simple
if ($permissions->currentUserCan('ta_manage_events')) {
    // Afficher ou traiter...
}

// Interruption automatique si permission manquante
$permissions->requireCapability('ta_manage_members');

// Verification sur un objet specifique
if ($permissions->currentUserCanOnObject('edit_post', $postId)) {
    // Modifier le post...
}

// Verifier si l'utilisateur est connecte
if (!$permissions->isLoggedIn()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}
```

### Roles disponibles

| Role               | Label                     | Acces principal                                    |
|--------------------|---------------------------|---------------------------------------------------|
| `ta_president`     | President de l'association | Acces complet, gestion association                |
| `ta_bureau`        | Membre du bureau           | Gestion evenements, membres, contenu              |
| `ta_membre_actif`  | Membre actif               | Evenements, annuaire, creation de contenu         |
| `ta_adherent`      | Adherent                   | Consultation evenements, modification profil      |

### Capabilities disponibles

```
ta_manage_association     - Gerer tous les parametres de l'association
ta_manage_members         - Creer, modifier et gerer les membres
ta_delete_members         - Supprimer des membres
ta_manage_events          - Creer et modifier des evenements
ta_delete_events          - Supprimer des evenements
ta_manage_finances        - Acceder et gerer les finances
ta_view_reports           - Consulter les rapports et statistiques
ta_manage_roles           - Gerer les roles et permissions
ta_send_notifications     - Envoyer des notifications
ta_manage_content         - Gerer le contenu du site
ta_publish_content        - Publier du contenu sans moderation
ta_create_content         - Creer du contenu soumis a moderation
ta_view_security_logs     - Consulter les journaux de securite
ta_view_events            - Consulter les evenements
ta_register_events        - S'inscrire aux evenements
ta_view_members_directory - Consulter l'annuaire des membres
ta_edit_own_profile       - Modifier son propre profil
```

---

## Hashing et encryption

### Mots de passe et tokens

```php
$hasher = ta_security()->getHasher();

// Hacher un mot de passe (lors de la creation de compte)
$hashedPassword = $hasher->hash($plainPassword);

// Verifier un mot de passe (lors de la connexion)
if ($hasher->verify($inputPassword, $hashedPassword)) {
    // Connexion autorisee

    // Verifier si le hash doit etre recalcule (mise a jour d'algorithme)
    if ($hasher->needsRehash($hashedPassword)) {
        $newHash = $hasher->hash($inputPassword);
        // Sauvegarder $newHash en base de donnees
    }
}

// Generer un token securise (lien de reinitialisation, cle API)
$resetToken = $hasher->generateToken(32);
```

### Chiffrement de donnees sensibles

```php
// Chiffrer avant stockage en base de donnees
$encryptedData = $hasher->encrypt($sensitiveData);
update_user_meta($userId, 'ta_sensitive_field', $encryptedData);

// Dechiffrer lors de la lecture
$stored    = get_user_meta($userId, 'ta_sensitive_field', true);
$plainData = $hasher->decrypt($stored);

// Signer des donnees pour verifier leur integrite
$signature = $hasher->sign($data, SECURE_AUTH_KEY);
// Stocker $data et $signature separement

// Verifier l'integrite
if (!$hasher->verifySignature($data, $signature, SECURE_AUTH_KEY)) {
    // Les donnees ont ete modifiees
}
```

---

## Logging de securite

Le `SecurityLoggerService` enregistre automatiquement :
- Les violations CSRF (via `CsrfProtectorService`)
- Les echecs d'authentification (via le hook `wp_login_failed`)
- Les acces refuses (via `PermissionCheckerService::requireCapability()`)

### Logging manuel

```php
$logger = ta_security()->getLogger();

// Log informatif
$logger->log('member_registered', 'Nouvel adherent inscrit.', $logger::LEVEL_INFO, [
    'user_id' => $userId,
]);

// Log d'avertissement
$logger->log('suspicious_activity', 'Tentatives repetees detectees.', $logger::LEVEL_WARNING, [
    'ip' => $ipAddress,
    'attempts' => $count,
]);

// Methodes dediees
$logger->logUnauthorizedAccess('ta_manage_finances', '/dashboard/finances');
$logger->logValidationFailure('registration_form', $validator->getErrors());
$logger->logPermissionChange($targetUserId, 'role: subscriber -> ta_membre_actif', $adminId);
```

### Consulter les logs

```php
// 50 derniers evenements tous niveaux
$events = $logger->getRecentEvents(50);

// Filtrer par niveau de severite
$errors   = $logger->getRecentEvents(20, $logger::LEVEL_ERROR);
$critical = $logger->getRecentEvents(10, $logger::LEVEL_CRITICAL);
```

---

## Regles a ne jamais enfreindre

Les regles suivantes sont non-negociables et doivent etre respectees
dans chaque contribution au theme.

### Entrees et sorties

- Ne JAMAIS afficher une donnee externe sans l'avoir echappee avec la fonction appropriee
- Ne JAMAIS inserer en base de donnees une donnee externe sans l'avoir sanitisee
- Ne JAMAIS utiliser `echo $_POST['champ']` directement dans un template
- Ne JAMAIS utiliser `$wpdb->query()` avec des variables non preparees ; utiliser `$wpdb->prepare()`

### Formulaires

- Tout formulaire POST doit inclure un token CSRF verifie cote serveur
- La verification CSRF doit preceder tout traitement de donnees soumises
- Ne jamais faire confiance aux champs hidden (ils peuvent etre modifies par l'utilisateur)

### Permissions

- Toujours verifier les permissions AVANT d'executer une action sensible
- Ne pas exposer d'informations sensibles a des utilisateurs non autorises
- Verifier les permissions sur les objets individuels (pas seulement le type)

### Cryptographie

- Ne jamais implementer sa propre cryptographie
- Utiliser PASSWORD_BCRYPT ou PASSWORD_ARGON2ID pour les mots de passe, jamais MD5 ou SHA1
- Ne jamais stocker des mots de passe en clair ou reversiblement
- Utiliser `hash_equals()` pour comparer des tokens (temps constant)

### Fichiers et chemins

- Valider et sanitiser les noms de fichiers avec `sanitize_file_name()`
- Bloquer les traversees de repertoire (`../`)
- Verifier le type MIME des fichiers uploades, pas seulement l'extension
- Ne jamais executer de fichiers uploades par l'utilisateur

### Logs et erreurs

- Ne jamais afficher les erreurs PHP en production (`WP_DEBUG` = false)
- Ne jamais logger de mots de passe ou de tokens en clair
- Ne jamais retourner des messages d'erreur detailles a l'utilisateur final

---

*Document maintenu par l'equipe de developpement du Theme Associatif.*
*Derniere mise a jour : version 1.0.0*
