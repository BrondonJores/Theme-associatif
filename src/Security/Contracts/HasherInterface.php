<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface HasherInterface
 *
 * Contrat pour le service de hashing et d'encryption des donnees sensibles.
 * Fournit des methodes securisees pour stocker des mots de passe, tokens,
 * et chiffrer des donnees confidentielles.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface HasherInterface
{
    /**
     * Hache une chaine avec un algorithme securise (bcrypt ou argon2id).
     * Le sel est genere automatiquement et incorpore dans le hash.
     *
     * @param  string $value La valeur en clair a hacher.
     * @return string Le hash securise.
     */
    public function hash(string $value): string;

    /**
     * Verifie qu'une valeur en clair correspond a un hash.
     *
     * @param  string $value La valeur en clair.
     * @param  string $hash  Le hash stocke.
     * @return bool   True si la valeur correspond au hash.
     */
    public function verify(string $value, string $hash): bool;

    /**
     * Verifie si un hash doit etre recalcule (algorithme ou cout obsolete).
     *
     * @param  string $hash Le hash a verifier.
     * @return bool   True si le hash doit etre recalcule.
     */
    public function needsRehash(string $hash): bool;

    /**
     * Genere un token aleatoire cryptographiquement sur.
     *
     * @param  int    $length La longueur du token en octets (avant encodage).
     * @return string Le token en hexadecimal.
     */
    public function generateToken(int $length = 32): string;

    /**
     * Chiffre une valeur avec AES-256-GCM.
     * Le vecteur d'initialisation (IV) est genere aleatoirement
     * et stocke avec la donnee chiffree.
     *
     * @param  string $value La valeur en clair a chiffrer.
     * @return string La donnee chiffree encodee en base64.
     * @throws \RuntimeException Si le chiffrement echoue.
     */
    public function encrypt(string $value): string;

    /**
     * Dechiffre une valeur prealablement chiffree avec encrypt().
     *
     * @param  string $encrypted La donnee chiffree encodee en base64.
     * @return string La valeur en clair.
     * @throws \RuntimeException Si le dechiffrement echoue ou si la donnee est corrompue.
     */
    public function decrypt(string $encrypted): string;

    /**
     * Cree une signature HMAC pour verifier l'integrite d'une donnee.
     *
     * @param  string $data   La donnee a signer.
     * @param  string $secret Le secret de signature (cle HMAC).
     * @return string La signature en hexadecimal.
     */
    public function sign(string $data, string $secret): string;

    /**
     * Verifie la signature HMAC d'une donnee.
     * Utilise une comparaison en temps constant pour eviter les attaques timing.
     *
     * @param  string $data      La donnee originale.
     * @param  string $signature La signature a verifier.
     * @param  string $secret    Le secret de signature.
     * @return bool   True si la signature est valide.
     */
    public function verifySignature(string $data, string $signature, string $secret): bool;
}
