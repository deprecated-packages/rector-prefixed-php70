<?php

declare (strict_types=1);
namespace PHPStan\Dependency\ExportedNode;

use JsonSerializable;
use PHPStan\Dependency\ExportedNode;
class ExportedTraitUseAdaptation implements \PHPStan\Dependency\ExportedNode, \JsonSerializable
{
    /** @var string|null */
    private $traitName;
    /** @var string */
    private $method;
    /** @var int|null */
    private $newModifier;
    /** @var string|null */
    private $newName;
    /** @var string[]|null */
    private $insteadOfs;
    /**
     * @param string|null $traitName
     * @param string $method
     * @param int|null $newModifier
     * @param string|null $newName
     * @param string[]|null $insteadOfs
     */
    private function __construct($traitName, string $method, $newModifier, $newName, $insteadOfs)
    {
        $this->traitName = $traitName;
        $this->method = $method;
        $this->newModifier = $newModifier;
        $this->newName = $newName;
        $this->insteadOfs = $insteadOfs;
    }
    /**
     * @return $this
     * @param string|null $traitName
     * @param int|null $newModifier
     * @param string|null $newName
     */
    public static function createAlias($traitName, string $method, $newModifier, $newName)
    {
        return new self($traitName, $method, $newModifier, $newName, null);
    }
    /**
     * @param string|null $traitName
     * @param string $method
     * @param string[] $insteadOfs
     * @return self
     */
    public static function createPrecedence($traitName, string $method, array $insteadOfs)
    {
        return new self($traitName, $method, null, null, $insteadOfs);
    }
    /**
     * @param \PHPStan\Dependency\ExportedNode $node
     */
    public function equals($node) : bool
    {
        if (!$node instanceof self) {
            return \false;
        }
        return $this->traitName === $node->traitName && $this->method === $node->method && $this->newModifier === $node->newModifier && $this->newName === $node->newName && $this->insteadOfs === $node->insteadOfs;
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties) : \PHPStan\Dependency\ExportedNode
    {
        return new self($properties['traitName'], $properties['method'], $properties['newModifier'], $properties['newName'], $properties['insteadOfs']);
    }
    /**
     * @param mixed[] $data
     * @return self
     */
    public static function decode(array $data) : \PHPStan\Dependency\ExportedNode
    {
        return new self($data['traitName'], $data['method'], $data['newModifier'], $data['newName'], $data['insteadOfs']);
    }
    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return ['type' => self::class, 'data' => ['traitName' => $this->traitName, 'method' => $this->method, 'newModifier' => $this->newModifier, 'newName' => $this->newName, 'insteadOfs' => $this->insteadOfs]];
    }
}
