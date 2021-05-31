<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDoc;

use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode;
use Stringable;
final class DoctrineAnnotationTagValueNode extends \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode
{
    /**
     * @var string
     */
    private $annotationClass;
    /**
     * @param array<mixed, mixed> $values
     * @param string|null $originalContent
     * @param string|null $silentKey
     */
    public function __construct(string $annotationClass, $originalContent = null, array $values = [], $silentKey = null)
    {
        $this->annotationClass = $annotationClass;
        $this->hasChanged = \true;
        parent::__construct($values, $originalContent, $silentKey);
    }
    public function __toString() : string
    {
        if (!$this->hasChanged) {
            if ($this->originalContent === null) {
                return '';
            }
            return $this->originalContent;
        }
        if ($this->values === []) {
            if ($this->originalContent === '()') {
                // empty brackets
                return $this->originalContent;
            }
            return '';
        }
        $itemContents = $this->printValuesContent($this->values);
        return \sprintf('(%s)', $itemContents);
    }
    public function getAnnotationClass() : string
    {
        return $this->annotationClass;
    }
}
