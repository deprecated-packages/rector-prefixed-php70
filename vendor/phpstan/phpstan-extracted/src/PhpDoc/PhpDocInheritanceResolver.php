<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\FileTypeMapper;
class PhpDocInheritanceResolver
{
    /** @var \PHPStan\Type\FileTypeMapper */
    private $fileTypeMapper;
    public function __construct(\PHPStan\Type\FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
    }
    /**
     * @param string|null $docComment
     * @param string|null $declaringTraitName
     */
    public function resolvePhpDocForProperty($docComment, \PHPStan\Reflection\ClassReflection $classReflection, string $classReflectionFileName, $declaringTraitName, string $propertyName) : \PHPStan\PhpDoc\ResolvedPhpDocBlock
    {
        $phpDocBlock = \PHPStan\PhpDoc\PhpDocBlock::resolvePhpDocBlockForProperty($docComment, $classReflection, null, $propertyName, $classReflectionFileName, null, [], []);
        return $this->docBlockTreeToResolvedDocBlock($phpDocBlock, $declaringTraitName, null);
    }
    /**
     * @param string|null $docComment
     * @param string $fileName
     * @param ClassReflection $classReflection
     * @param string|null $declaringTraitName
     * @param string $methodName
     * @param array<int, string> $positionalParameterNames
     * @return ResolvedPhpDocBlock
     */
    public function resolvePhpDocForMethod($docComment, string $fileName, \PHPStan\Reflection\ClassReflection $classReflection, $declaringTraitName, string $methodName, array $positionalParameterNames) : \PHPStan\PhpDoc\ResolvedPhpDocBlock
    {
        $phpDocBlock = \PHPStan\PhpDoc\PhpDocBlock::resolvePhpDocBlockForMethod($docComment, $classReflection, $declaringTraitName, $methodName, $fileName, null, $positionalParameterNames, $positionalParameterNames);
        return $this->docBlockTreeToResolvedDocBlock($phpDocBlock, $phpDocBlock->getTrait(), $methodName);
    }
    /**
     * @param string|null $traitName
     * @param string|null $functionName
     */
    private function docBlockTreeToResolvedDocBlock(\PHPStan\PhpDoc\PhpDocBlock $phpDocBlock, $traitName, $functionName) : \PHPStan\PhpDoc\ResolvedPhpDocBlock
    {
        $parents = [];
        $parentPhpDocBlocks = [];
        foreach ($phpDocBlock->getParents() as $parentPhpDocBlock) {
            if ($parentPhpDocBlock->getClassReflection()->isBuiltin() && $functionName !== null && \strtolower($functionName) === '__construct') {
                continue;
            }
            $parents[] = $this->docBlockTreeToResolvedDocBlock($parentPhpDocBlock, $parentPhpDocBlock->getTrait(), $functionName);
            $parentPhpDocBlocks[] = $parentPhpDocBlock;
        }
        $oneResolvedDockBlock = $this->docBlockToResolvedDocBlock($phpDocBlock, $traitName, $functionName);
        return $oneResolvedDockBlock->merge($parents, $parentPhpDocBlocks);
    }
    /**
     * @param string|null $traitName
     * @param string|null $functionName
     */
    private function docBlockToResolvedDocBlock(\PHPStan\PhpDoc\PhpDocBlock $phpDocBlock, $traitName, $functionName) : \PHPStan\PhpDoc\ResolvedPhpDocBlock
    {
        $classReflection = $phpDocBlock->getClassReflection();
        return $this->fileTypeMapper->getResolvedPhpDoc($phpDocBlock->getFile(), $classReflection->getName(), $traitName, $functionName, $phpDocBlock->getDocComment());
    }
}
