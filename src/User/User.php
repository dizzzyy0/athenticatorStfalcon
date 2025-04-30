<?php

declare(strict_types=1);

namespace App\User;

use App\User\Support\UserRepository;
use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use RuntimeException;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    private const int PERIOD = 30;

    private const int DIGITS = 6;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;


    /**
     * @var non-empty-string
     */
    #[ORM\Column(length: 180)]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $secretKey;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private DateTime $lastLogin;

    #[ORM\Column(nullable: true)]
    private ?string $picturePath;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     *
     */
    public function getUserIdentifier(): string
    {
        $id = $this->id->toString();

        if (empty($id)) {
            throw new LogicException("User Id can't be empty");
        }

        return $id;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(?string  $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->secretKey !== null;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        /** @var string $key */
        $key = $_ENV['ENCRYPTION_KEY'] ?? throw new LogicException('Encryption key is not configured');
        $encryptionService = new EncryptionService($key);
        $decryptedSecret = $encryptionService->decryptSecret($this->secretKey ?? throw new RuntimeException('Secret key is not configured'));
        return new TotpConfiguration(
            $decryptedSecret,
            TotpConfiguration::ALGORITHM_SHA1,
            self::PERIOD,
            self::DIGITS
        );
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin): self{
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(?string $picturePath): self
    {
        $this->picturePath = $picturePath;
        return $this;
    }
}
