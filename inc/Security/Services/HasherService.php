<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\HasherInterface;
use RuntimeException;

/**
 * Class HasherService
 *
 * Implementation du service de hashing et d'encryption des donnees sensibles.
 *
 * Algorithmes utilises :
 * - Hashing de mots de passe : PASSWORD_BCRYPT (facteur de cout configurable)
 * - Generation de tokens : random_bytes() avec CSPRNG
 * - Chiffrement symetrique : AES-256-GCM (authentifie, resiste a la falsification)
 * - Signature HMAC : SHA-256 avec comparaison en temps constant
 *
 * La cle de chiffrement est derivee de AUTH_KEY et SECURE_AUTH_KEY
 * de WordPress (definis dans wp-config.php) pour eviter de stocker
 * une cle supplementaire.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class HasherService implements HasherInterface
{
    /**
     * L'algorithme de chiffrement symetrique utilise.
     * AES-256-GCM offre le chiffrement authentifie (AEAD).
     */
    private const CIPHER_ALGORITHM = 'aes-256-gcm';

    /**
     * La longueur du vecteur d'initialisation pour AES-256-GCM.
     * GCM recommande un IV de 12 octets (96 bits).
     */
    private const IV_LENGTH = 12;

    /**
     * La longueur du tag d'authentification GCM en octets.
     */
    private const TAG_LENGTH = 16;

    /**
     * Le facteur de cout pour bcrypt (entre 10 et 14 recommande).
     * Chaque increment double le temps de calcul.
     *
     * @var int
     */
    private int $bcryptCost;

    /**
     * La cle de chiffrement derivee des secrets WordPress.
     * Calculee une seule fois lors de la premiere utilisation.
     *
     * @var string|null
     */
    private ?string $encryptionKey = null;

    /**
     * Constructeur du service de hashing.
     *
     * @param int $bcryptCost Le facteur de cout bcrypt (defaut: 12).
     */
    public function __construct(int $bcryptCost = 12)
    {
        if ($bcryptCost < 10 || $bcryptCost > 14) {
            $bcryptCost = 12;
        }

        $this->bcryptCost = $bcryptCost;
    }

    /**
     * {@inheritdoc}
     *
     * Utilise PASSWORD_BCRYPT avec un sel genere automatiquement.
     * Le sel est incorpore dans la chaine de hash resultante.
     */
    public function hash(string $value): string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost]);

        if ($hash === false) {
            throw new RuntimeException('Echec du hachage : password_hash() a retourne false.');
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     *
     * Utilise password_verify() qui est resistant aux attaques timing.
     */
    public function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise password_needs_rehash() pour detecter si le hash
     * a ete cree avec un algorithme ou un cout obsolete.
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost]);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise random_bytes() qui appelle le CSPRNG du systeme d'exploitation.
     * Le resultat est encode en hexadecimal pour la portabilite.
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * {@inheritdoc}
     *
     * Processus de chiffrement :
     * 1. Generation d'un IV aleatoire unique (12 octets pour GCM)
     * 2. Chiffrement AES-256-GCM avec tag d'authentification
     * 3. Concatenation IV + tag + donnees chiffrees
     * 4. Encodage en base64 pour le stockage
     */
    public function encrypt(string $value): string
    {
        $key = $this->getEncryptionKey();
        $iv  = random_bytes(self::IV_LENGTH);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            self::CIPHER_ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($encrypted === false) {
            throw new RuntimeException('Echec du chiffrement : openssl_encrypt() a retourne false.');
        }

        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * {@inheritdoc}
     *
     * Processus de dechiffrement :
     * 1. Decodage base64
     * 2. Extraction de l'IV (12 octets), du tag (16 octets) et des donnees
     * 3. Dechiffrement AES-256-GCM avec verification du tag
     */
    public function decrypt(string $encrypted): string
    {
        $key  = $this->getEncryptionKey();
        $data = base64_decode($encrypted, true);

        if ($data === false) {
            throw new RuntimeException('Echec du dechiffrement : encodage base64 invalide.');
        }

        $minLength = self::IV_LENGTH + self::TAG_LENGTH + 1;

        if (strlen($data) < $minLength) {
            throw new RuntimeException('Echec du dechiffrement : donnees trop courtes ou corrompues.');
        }

        $iv         = substr($data, 0, self::IV_LENGTH);
        $tag        = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($data, self::IV_LENGTH + self::TAG_LENGTH);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER_ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new RuntimeException('Echec du dechiffrement : donnees corrompues ou cle invalide.');
        }

        return $decrypted;
    }

    /**
     * {@inheritdoc}
     *
     * Utilise hash_hmac() avec SHA-256 pour creer une signature
     * cryptographique qui garantit l'integrite de la donnee.
     */
    public function sign(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise hash_equals() pour une comparaison en temps constant
     * qui evite les attaques par timing (timing attack).
     */
    public function verifySignature(string $data, string $signature, string $secret): bool
    {
        $expected = $this->sign($data, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Derive et retourne la cle de chiffrement AES-256 (32 octets).
     * La cle est derivee des secrets WordPress via HKDF (hash-based key derivation).
     * Cela evite d'avoir a stocker une cle de chiffrement supplementaire.
     *
     * @return string La cle de 32 octets pour AES-256.
     */
    private function getEncryptionKey(): string
    {
        if ($this->encryptionKey !== null) {
            return $this->encryptionKey;
        }

        $authKey       = defined('AUTH_KEY') ? AUTH_KEY : 'default-auth-key';
        $secureAuthKey = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 'default-secure-auth-key';
        $salt          = defined('AUTH_SALT') ? AUTH_SALT : 'default-auth-salt';

        $masterKey = hash_hmac('sha256', $authKey . $secureAuthKey, $salt, true);

        $this->encryptionKey = hash_hkdf('sha256', $masterKey, 32, 'theme-associatif-encryption-v1');

        return $this->encryptionKey;
    }
}
