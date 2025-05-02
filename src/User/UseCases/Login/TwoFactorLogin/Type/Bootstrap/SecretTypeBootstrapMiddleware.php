<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Type\Bootstrap;
use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use App\User\UseCases\Login\TwoFactorLogin\Type\SecretKeyType;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Types\Type;

final readonly class SecretTypeBootstrapMiddleware implements Middleware
{

    public function __construct(
        private EncryptionService $encryptionService,
    ){
    }

    public function wrap(Driver $driver): Driver
    {
        /** @var SecretKeyType $type */
        $type = Type::getType(SecretKeyType::NAME);

        $type->setEncryptionService($this->encryptionService);

        return $driver;
    }
}
