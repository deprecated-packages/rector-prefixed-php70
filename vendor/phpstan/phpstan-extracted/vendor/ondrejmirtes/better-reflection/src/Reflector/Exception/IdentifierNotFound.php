<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflector\Exception;

use PHPStan\BetterReflection\Identifier\Identifier;
use RuntimeException;
use function sprintf;
class IdentifierNotFound extends \RuntimeException
{
    /** @var Identifier */
    private $identifier;
    public function __construct(string $message, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        parent::__construct($message);
        $this->identifier = $identifier;
    }
    public function getIdentifier() : \PHPStan\BetterReflection\Identifier\Identifier
    {
        return $this->identifier;
    }
    /**
     * @return $this
     */
    public static function fromIdentifier(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        return new self(\sprintf('%s "%s" could not be found in the located source', $identifier->getType()->getName(), $identifier->getName()), $identifier);
    }
}
