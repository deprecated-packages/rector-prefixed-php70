<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Annotation;

use RectorPrefix20210616\Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use ReflectionClass;
/**
 * @see \Rector\Tests\ChangesReporting\Annotation\AnnotationExtractorTest
 */
final class AnnotationExtractor
{
    /**
     * @param class-string<RectorInterface> $className
     * @return string|null
     */
    public function extractAnnotationFromClass(string $className, string $annotation)
    {
        $reflectionClass = new \ReflectionClass($className);
        $docComment = $reflectionClass->getDocComment();
        if (!\is_string($docComment)) {
            return null;
        }
        // @see https://3v4l.org/ouYfB
        // uses 'r?\n' instead of '$' because windows compat
        $pattern = '#' . \preg_quote($annotation, '#') . '\\s+(?<content>.*?)\\r?\\n#m';
        $matches = \RectorPrefix20210616\Nette\Utils\Strings::match($docComment, $pattern);
        return $matches['content'] ?? null;
    }
}
