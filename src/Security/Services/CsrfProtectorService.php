<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\CsrfProtectorInterface;
use ThemeAssociatif\Security\Contracts\NonceManagerInterface;
use ThemeAssociatif\Security\Contracts\SecurityLoggerInterface;

/**
 * Class CsrfProtectorService
 *
 * Implementation de la protection CSRF (Cross-Site Request Forgery).
 * S'appuie sur le systeme de nonces WordPress pour generer et valider
 * des tokens CSRF uniques par formulaire et par utilisateur.
 *
 * La protection est automatique : tous les formulaires qui utilisent
 * protectForm() ou le hook 'ta_form_open' reçoivent automatiquement
 * un token CSRF invisible.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class CsrfProtectorService implements CsrfProtectorInterface
{
    /**
     * Le gestionnaire de nonces WordPress.
     *
     * @var NonceManagerInterface
     */
    private NonceManagerInterface $nonceManager;

    /**
     * Le service de logging de securite.
     *
     * @var SecurityLoggerInterface
     */
    private SecurityLoggerInterface $logger;

    /**
     * Constructeur avec injection de dependances.
     *
     * @param NonceManagerInterface   $nonceManager Le gestionnaire de nonces.
     * @param SecurityLoggerInterface $logger       Le service de logging.
     */
    public function __construct(
        NonceManagerInterface $nonceManager,
        SecurityLoggerInterface $logger
    ) {
        $this->nonceManager = $nonceManager;
        $this->logger       = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * Enregistre les hooks WordPress necessaires pour l'injection
     * automatique des tokens CSRF dans les formulaires du theme.
     */
    public function initialize(): void
    {
        add_action('ta_form_fields', [$this, 'injectFormToken']);
    }

    /**
     * Hook WordPress : injecte le champ CSRF dans un formulaire.
     * A appeler via do_action('ta_form_fields', $formName) dans les templates.
     *
     * @param  string $formName Le nom du formulaire.
     * @return void
     */
    public function injectFormToken(string $formName): void
    {
        $action = $this->generateAction($formName);

        echo wp_kses(
            $this->protectForm($action, '_csrf_token'),
            ['input' => ['type' => true, 'id' => true, 'name' => true, 'value' => true]]
        );
    }

    /**
     * {@inheritdoc}
     *
     * Le champ retourne est un input hidden avec le token nonce.
     * Il doit etre inclus dans chaque formulaire soumis par POST.
     */
    public function protectForm(string $action, string $fieldName = '_csrf_token'): string
    {
        return $this->nonceManager->field($action, $fieldName, false, false);
    }

    /**
     * {@inheritdoc}
     *
     * Extrait le token du tableau POST et le verifie contre l'action attendue.
     */
    public function validateRequest(string $action, string $fieldName = '_csrf_token'): bool
    {
        return $this->nonceManager->verifyRequest($action, $fieldName);
    }

    /**
     * {@inheritdoc}
     *
     * Enregistre la violation CSRF dans les logs avant d'interrompre
     * l'execution. N'expose pas de details sur la cause de l'echec
     * dans le message d'erreur affiche a l'utilisateur.
     */
    public function assertValid(string $action, string $fieldName = '_csrf_token'): void
    {
        if (!$this->validateRequest($action, $fieldName)) {
            $ipAddress = $this->getClientIpAddress();

            $this->logger->logCsrfViolation($action, $ipAddress);

            wp_die(
                esc_html__('La verification de securite a echoue. Veuillez retourner a la page precedente et reessayer.', 'theme-associatif'),
                esc_html__('Acces refuse', 'theme-associatif'),
                ['response' => 403, 'back_link' => true]
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * L'action generee est unique par formulaire et par utilisateur
     * pour maximiser la securite CSRF.
     */
    public function generateAction(string $formName): string
    {
        $userId = get_current_user_id();

        return sprintf('csrf_%s_%d', sanitize_key($formName), $userId);
    }

    /**
     * Recupere l'adresse IP du client de maniere securisee.
     * Prend en compte les proxies inverses courants mais ne fait pas
     * confiance a tous les headers HTTP pour eviter le spoofing.
     *
     * @return string L'adresse IP detectee ou 'unknown'.
     */
    private function getClientIpAddress(): string
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!is_string($ipAddress) || !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return 'unknown';
        }

        return $ipAddress;
    }
}
