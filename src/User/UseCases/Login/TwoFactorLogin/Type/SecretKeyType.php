<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Type;

use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;
use Webmozart\Assert\Assert;

final class SecretKeyType extends Type
{
    public const NAME = 'secret_key';

    private EncryptionService $encryptionService;

    public function setEncryptionService(EncryptionService $encryptionService): void
    {
        $this->encryptionService = $encryptionService;
    }

    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if(null === $value) {
            return null;
        }

        Assert::string($value);

        return $this->encryptionService->encryptSecret($value);
    }

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        if(null === $value) {
            return null;
        }

        Assert::string($value);

        return $this->encryptionService->decryptSecret($value);
    }


    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    #[Override]
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        // this is required to make doctrine migrations diff command
        // not to generate alter column statement every time
        return true;
    }
}
