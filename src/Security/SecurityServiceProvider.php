<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security;

use ThemeAssociatif\Security\Contracts\CsrfProtectorInterface;
use ThemeAssociatif\Security\Contracts\EscaperInterface;
use ThemeAssociatif\Security\Contracts\HasherInterface;
use ThemeAssociatif\Security\Contracts\NonceManagerInterface;
use ThemeAssociatif\Security\Contracts\PermissionCheckerInterface;
use ThemeAssociatif\Security\Contracts\RoleManagerInterface;
use ThemeAssociatif\Security\Contracts\SanitizerInterface;
use ThemeAssociatif\Security\Contracts\SecurityLoggerInterface;
use ThemeAssociatif\Security\Contracts\ValidatorInterface;
use ThemeAssociatif\Security\Services\CsrfProtectorService;
use ThemeAssociatif\Security\Services\EscaperService;
use ThemeAssociatif\Security\Services\HasherService;
use ThemeAssociatif\Security\Services\NonceManagerService;
use ThemeAssociatif\Security\Services\PermissionCheckerService;
use ThemeAssociatif\Security\Services\RoleManagerService;
use ThemeAssociatif\Security\Services\SanitizerService;
use ThemeAssociatif\Security\Services\SecurityLoggerService;
use ThemeAssociatif\Security\Services\ValidatorService;

/**
 * Class SecurityServiceProvider
 *
 * Point d'entree unique du systeme de securite du theme.
 * Ce fournisseur de services instancie, configure et lie tous les
 * composants de securite entre eux selon le principe d'inversion
 * de dependances (Dependency Inversion Principle).
 *
 * Toutes les dependances sont injectees par constructeur, jamais
 * instanciees directement dans le code consommateur.
 *
 * Utilisation dans functions.php :
 *
 *   $security = SecurityServiceProvider::getInstance();
 *   $sanitizer = $security->getSanitizer();
 *   $validator = $security->getValidator();
 *
 * @package ThemeAssociatif\Security
 * @since   1.0.0
 */
class SecurityServiceProvider
{
    /**
     * Instance unique du provider (pattern Singleton).
     * Garantit qu'un seul systeme de securite est actif par execution.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * @var SanitizerInterface
     */
    private SanitizerInterface $sanitizer;

    /**
     * @var EscaperInterface
     */
    private EscaperInterface $escaper;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var NonceManagerInterface
     */
    private NonceManagerInterface $nonceManager;

    /**
     * @var CsrfProtectorInterface
     */
    private CsrfProtectorInterface $csrfProtector;

    /**
     * @var RoleManagerInterface
     */
    private RoleManagerInterface $roleManager;

    /**
     * @var PermissionCheckerInterface
     */
    private PermissionCheckerInterface $permissionChecker;

    /**
     * @var HasherInterface
     */
    private HasherInterface $hasher;

    /**
     * @var SecurityLoggerInterface
     */
    private SecurityLoggerInterface $logger;

    /**
     * Constructeur prive : construit et injecte toutes les dependances.
     * L'ordre d'instanciation respecte les dependances entre services.
     */
    private function __construct()
    {
        $this->logger            = new SecurityLoggerService();
        $this->sanitizer         = new SanitizerService();
        $this->escaper           = new EscaperService();
        $this->validator         = new ValidatorService();
        $this->nonceManager      = new NonceManagerService();
        $this->csrfProtector     = new CsrfProtectorService($this->nonceManager, $this->logger);
        $this->roleManager       = new RoleManagerService();
        $this->permissionChecker = new PermissionCheckerService();
        $this->hasher            = new HasherService();

        $this->registerHooks();
    }

    /**
     * Retourne l'instance unique du provider.
     * Cree l'instance lors du premier appel (lazy initialization).
     *
     * @return self L'instance unique.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retourne le service de sanitization.
     *
     * @return SanitizerInterface
     */
    public function getSanitizer(): SanitizerInterface
    {
        return $this->sanitizer;
    }

    /**
     * Retourne le service d'echappement.
     *
     * @return EscaperInterface
     */
    public function getEscaper(): EscaperInterface
    {
        return $this->escaper;
    }

    /**
     * Retourne le service de validation.
     *
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * Retourne le gestionnaire de nonces.
     *
     * @return NonceManagerInterface
     */
    public function getNonceManager(): NonceManagerInterface
    {
        return $this->nonceManager;
    }

    /**
     * Retourne le service de protection CSRF.
     *
     * @return CsrfProtectorInterface
     */
    public function getCsrfProtector(): CsrfProtectorInterface
    {
        return $this->csrfProtector;
    }

    /**
     * Retourne le gestionnaire de roles.
     *
     * @return RoleManagerInterface
     */
    public function getRoleManager(): RoleManagerInterface
    {
        return $this->roleManager;
    }

    /**
     * Retourne le service de verification des permissions.
     *
     * @return PermissionCheckerInterface
     */
    public function getPermissionChecker(): PermissionCheckerInterface
    {
        return $this->permissionChecker;
    }

    /**
     * Retourne le service de hashing et encryption.
     *
     * @return HasherInterface
     */
    public function getHasher(): HasherInterface
    {
        return $this->hasher;
    }

    /**
     * Retourne le service de logging de securite.
     *
     * @return SecurityLoggerInterface
     */
    public function getLogger(): SecurityLoggerInterface
    {
        return $this->logger;
    }

    /**
     * Enregistre les hooks WordPress necessaires au systeme de securite.
     * Appele une seule fois lors de l'initialisation du provider.
     *
     * @return void
     */
    private function registerHooks(): void
    {
        $this->csrfProtector->initialize();

        add_action('after_switch_theme', [$this->roleManager, 'registerRoles']);
        add_action('switch_theme', [$this->roleManager, 'removeRoles']);

        add_action('wp_login_failed', function (string $username): void {
            $ipAddress = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
                ? $_SERVER['REMOTE_ADDR']
                : 'unknown';

            $this->logger->logAuthFailure($username, $ipAddress);
        });

        add_action('user_register', function (int $userId): void {
            $this->permissionChecker->flushCache($userId);
        });
    }

    /**
     * Empeche le clonage de l'instance (pattern Singleton).
     *
     * @return void
     */
    private function __clone(): void
    {
    }
}
