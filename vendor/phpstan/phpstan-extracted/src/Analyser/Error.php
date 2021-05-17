<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

class Error implements \JsonSerializable
{
    /** @var string */
    private $message;
    /** @var string */
    private $file;
    /** @var int|null */
    private $line;
    /** @var bool|\Throwable */
    private $canBeIgnored;
    /** @var string|null */
    private $filePath;
    /** @var string|null */
    private $traitFilePath;
    /** @var string|null */
    private $tip;
    /** @var int|null */
    private $nodeLine;
    /** @phpstan-var class-string<\PhpParser\Node>|null */
    private $nodeType;
    /** @var string|null */
    private $identifier;
    /** @var mixed[] */
    private $metadata;
    /**
     * Error constructor.
     *
     * @param string $message
     * @param string $file
     * @param int|null $line
     * @param bool|\Throwable $canBeIgnored
     * @param string|null $filePath
     * @param string|null $traitFilePath
     * @param string|null $tip
     * @param int|null $nodeLine
     * @param class-string<\PhpParser\Node>|null $nodeType
     * @param string|null $identifier
     * @param mixed[] $metadata
     */
    public function __construct(string $message, string $file, $line = null, $canBeIgnored = \true, $filePath = null, $traitFilePath = null, $tip = null, $nodeLine = null, $nodeType = null, $identifier = null, array $metadata = [])
    {
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->canBeIgnored = $canBeIgnored;
        $this->filePath = $filePath;
        $this->traitFilePath = $traitFilePath;
        $this->tip = $tip;
        $this->nodeLine = $nodeLine;
        $this->nodeType = $nodeType;
        $this->identifier = $identifier;
        $this->metadata = $metadata;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getFile() : string
    {
        return $this->file;
    }
    public function getFilePath() : string
    {
        if ($this->filePath === null) {
            return $this->file;
        }
        return $this->filePath;
    }
    /**
     * @return $this
     */
    public function changeFilePath(string $newFilePath)
    {
        if ($this->traitFilePath !== null) {
            throw new \PHPStan\ShouldNotHappenException('Errors in traits not yet supported');
        }
        return new self($this->message, $newFilePath, $this->line, $this->canBeIgnored, $newFilePath, null, $this->tip, $this->nodeLine, $this->nodeType, $this->identifier, $this->metadata);
    }
    /**
     * @return $this
     */
    public function changeTraitFilePath(string $newFilePath)
    {
        return new self($this->message, $this->file, $this->line, $this->canBeIgnored, $this->filePath, $newFilePath, $this->tip, $this->nodeLine, $this->nodeType, $this->identifier, $this->metadata);
    }
    /**
     * @return string|null
     */
    public function getTraitFilePath()
    {
        return $this->traitFilePath;
    }
    /**
     * @return int|null
     */
    public function getLine()
    {
        return $this->line;
    }
    public function canBeIgnored() : bool
    {
        return $this->canBeIgnored === \true;
    }
    public function hasNonIgnorableException() : bool
    {
        return $this->canBeIgnored instanceof \Throwable;
    }
    /**
     * @return string|null
     */
    public function getTip()
    {
        return $this->tip;
    }
    /**
     * @return $this
     */
    public function withoutTip()
    {
        if ($this->tip === null) {
            return $this;
        }
        return new self($this->message, $this->file, $this->line, $this->canBeIgnored, $this->filePath, $this->traitFilePath, null, $this->nodeLine, $this->nodeType);
    }
    /**
     * @return int|null
     */
    public function getNodeLine()
    {
        return $this->nodeLine;
    }
    /**
     * @return string|null
     */
    public function getNodeType()
    {
        return $this->nodeType;
    }
    /**
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    /**
     * @return mixed[]
     */
    public function getMetadata() : array
    {
        return $this->metadata;
    }
    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return ['message' => $this->message, 'file' => $this->file, 'line' => $this->line, 'canBeIgnored' => \is_bool($this->canBeIgnored) ? $this->canBeIgnored : 'exception', 'filePath' => $this->filePath, 'traitFilePath' => $this->traitFilePath, 'tip' => $this->tip, 'nodeLine' => $this->nodeLine, 'nodeType' => $this->nodeType, 'identifier' => $this->identifier, 'metadata' => $this->metadata];
    }
    /**
     * @param mixed[] $json
     * @return self
     */
    public static function decode(array $json)
    {
        return new self($json['message'], $json['file'], $json['line'], $json['canBeIgnored'] === 'exception' ? new \Exception() : $json['canBeIgnored'], $json['filePath'], $json['traitFilePath'], $json['tip'], $json['nodeLine'] ?? null, $json['nodeType'] ?? null, $json['identifier'] ?? null, $json['metadata'] ?? []);
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['message'], $properties['file'], $properties['line'], $properties['canBeIgnored'], $properties['filePath'], $properties['traitFilePath'], $properties['tip'], $properties['nodeLine'] ?? null, $properties['nodeType'] ?? null, $properties['identifier'] ?? null, $properties['metadata'] ?? []);
    }
}
