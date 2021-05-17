<?php

declare (strict_types=1);
namespace PHPStan\Rules;

class RuleErrorBuilder
{
    const TYPE_MESSAGE = 1;
    const TYPE_LINE = 2;
    const TYPE_FILE = 4;
    const TYPE_TIP = 8;
    const TYPE_IDENTIFIER = 16;
    const TYPE_METADATA = 32;
    const TYPE_NON_IGNORABLE = 64;
    /** @var int */
    private $type;
    /** @var mixed[] */
    private $properties;
    private function __construct(string $message)
    {
        $this->properties['message'] = $message;
        $this->type = self::TYPE_MESSAGE;
    }
    /**
     * @return array<int, array{string, string|null, string|null, string|null}>
     */
    public static function getRuleErrorTypes() : array
    {
        return [self::TYPE_MESSAGE => [\PHPStan\Rules\RuleError::class, 'message', 'string', 'string'], self::TYPE_LINE => [\PHPStan\Rules\LineRuleError::class, 'line', 'int', 'int'], self::TYPE_FILE => [\PHPStan\Rules\FileRuleError::class, 'file', 'string', 'string'], self::TYPE_TIP => [\PHPStan\Rules\TipRuleError::class, 'tip', 'string', 'string'], self::TYPE_IDENTIFIER => [\PHPStan\Rules\IdentifierRuleError::class, 'identifier', 'string', 'string'], self::TYPE_METADATA => [\PHPStan\Rules\MetadataRuleError::class, 'metadata', 'array', 'mixed[]'], self::TYPE_NON_IGNORABLE => [\PHPStan\Rules\NonIgnorableRuleError::class, null, null, null]];
    }
    /**
     * @return $this
     */
    public static function message(string $message)
    {
        return new self($message);
    }
    /**
     * @return $this
     */
    public function line(int $line)
    {
        $this->properties['line'] = $line;
        $this->type |= self::TYPE_LINE;
        return $this;
    }
    /**
     * @return $this
     */
    public function file(string $file)
    {
        $this->properties['file'] = $file;
        $this->type |= self::TYPE_FILE;
        return $this;
    }
    /**
     * @return $this
     */
    public function tip(string $tip)
    {
        $this->properties['tip'] = $tip;
        $this->type |= self::TYPE_TIP;
        return $this;
    }
    /**
     * @return $this
     */
    public function discoveringSymbolsTip()
    {
        return $this->tip('Learn more at https://phpstan.org/user-guide/discovering-symbols');
    }
    /**
     * @return $this
     */
    public function identifier(string $identifier)
    {
        $this->properties['identifier'] = $identifier;
        $this->type |= self::TYPE_IDENTIFIER;
        return $this;
    }
    /**
     * @param mixed[] $metadata
     * @return $this
     */
    public function metadata(array $metadata)
    {
        $this->properties['metadata'] = $metadata;
        $this->type |= self::TYPE_METADATA;
        return $this;
    }
    /**
     * @return $this
     */
    public function nonIgnorable()
    {
        $this->type |= self::TYPE_NON_IGNORABLE;
        return $this;
    }
    public function build() : \PHPStan\Rules\RuleError
    {
        /** @var class-string<RuleError> $className */
        $className = \sprintf('PHPStan\\Rules\\RuleErrors\\RuleError%d', $this->type);
        if (!\class_exists($className)) {
            throw new \PHPStan\ShouldNotHappenException(\sprintf('Class %s does not exist.', $className));
        }
        $ruleError = new $className();
        foreach ($this->properties as $propertyName => $value) {
            $ruleError->{$propertyName} = $value;
        }
        return $ruleError;
    }
}
