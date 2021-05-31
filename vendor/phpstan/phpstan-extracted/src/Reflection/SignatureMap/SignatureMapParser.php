<?php

declare (strict_types=1);
namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
class SignatureMapParser
{
    /** @var \PHPStan\PhpDoc\TypeStringResolver */
    private $typeStringResolver;
    public function __construct(\PHPStan\PhpDoc\TypeStringResolver $typeNodeResolver)
    {
        $this->typeStringResolver = $typeNodeResolver;
    }
    /**
     * @param mixed[] $map
     * @param string|null $className
     * @return \PHPStan\Reflection\SignatureMap\FunctionSignature
     */
    public function getFunctionSignature(array $map, $className) : \PHPStan\Reflection\SignatureMap\FunctionSignature
    {
        $parameterSignatures = $this->getParameters(\array_slice($map, 1));
        $hasVariadic = \false;
        foreach ($parameterSignatures as $parameterSignature) {
            if ($parameterSignature->isVariadic()) {
                $hasVariadic = \true;
                break;
            }
        }
        return new \PHPStan\Reflection\SignatureMap\FunctionSignature($parameterSignatures, $this->getTypeFromString($map[0], $className), new \PHPStan\Type\MixedType(), $hasVariadic);
    }
    /**
     * @param string|null $className
     */
    private function getTypeFromString(string $typeString, $className) : \PHPStan\Type\Type
    {
        if ($typeString === '') {
            return new \PHPStan\Type\MixedType(\true);
        }
        return $this->typeStringResolver->resolve($typeString, new \PHPStan\Analyser\NameScope(null, [], $className));
    }
    /**
     * @param array<string, string> $parameterMap
     * @return array<int, \PHPStan\Reflection\SignatureMap\ParameterSignature>
     */
    private function getParameters(array $parameterMap) : array
    {
        $parameterSignatures = [];
        foreach ($parameterMap as $parameterName => $typeString) {
            list($name, $isOptional, $passedByReference, $isVariadic) = $this->getParameterInfoFromName($parameterName);
            $parameterSignatures[] = new \PHPStan\Reflection\SignatureMap\ParameterSignature($name, $isOptional, $this->getTypeFromString($typeString, null), new \PHPStan\Type\MixedType(), $passedByReference, $isVariadic);
        }
        return $parameterSignatures;
    }
    /**
     * @param string $parameterNameString
     * @return mixed[]
     */
    private function getParameterInfoFromName(string $parameterNameString) : array
    {
        $matches = \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($parameterNameString, '#^(?P<reference>&(?:\\.\\.\\.)?r?w?_?)?(?P<variadic>\\.\\.\\.)?(?P<name>[^=]+)?(?P<optional>=)?($)#');
        if ($matches === null || !isset($matches['optional'])) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $isVariadic = $matches['variadic'] !== '';
        $reference = $matches['reference'];
        if (\strpos($reference, '&...') === 0) {
            $reference = '&' . \substr($reference, 4);
            $isVariadic = \true;
        }
        if (\strpos($reference, '&rw') === 0) {
            $passedByReference = \PHPStan\Reflection\PassedByReference::createReadsArgument();
        } elseif (\strpos($reference, '&w') === 0) {
            $passedByReference = \PHPStan\Reflection\PassedByReference::createCreatesNewVariable();
        } else {
            $passedByReference = \PHPStan\Reflection\PassedByReference::createNo();
        }
        $isOptional = $isVariadic || $matches['optional'] !== '';
        $name = $matches['name'] !== '' ? $matches['name'] : '...';
        return [$name, $isOptional, $passedByReference, $isVariadic];
    }
}
