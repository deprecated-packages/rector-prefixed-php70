<?php

declare (strict_types=1);
namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Php\PhpVersion;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypehintHelper;
class FunctionSignatureMapProvider implements \PHPStan\Reflection\SignatureMap\SignatureMapProvider
{
    /** @var \PHPStan\Reflection\SignatureMap\SignatureMapParser */
    private $parser;
    /** @var PhpVersion */
    private $phpVersion;
    /** @var mixed[]|null */
    private $signatureMap = null;
    /** @var array<string, array{hasSideEffects: bool}>|null */
    private $functionMetadata = null;
    public function __construct(\PHPStan\Reflection\SignatureMap\SignatureMapParser $parser, \PHPStan\Php\PhpVersion $phpVersion)
    {
        $this->parser = $parser;
        $this->phpVersion = $phpVersion;
    }
    public function hasMethodSignature(string $className, string $methodName, int $variant = 0) : bool
    {
        return $this->hasFunctionSignature(\sprintf('%s::%s', $className, $methodName), $variant);
    }
    public function hasFunctionSignature(string $name, int $variant = 0) : bool
    {
        $signatureMap = $this->getSignatureMap();
        if ($variant > 0) {
            $name .= '\'' . $variant;
        }
        return \array_key_exists(\strtolower($name), $signatureMap);
    }
    /**
     * @param \ReflectionMethod|null $reflectionMethod
     */
    public function getMethodSignature(string $className, string $methodName, $reflectionMethod, int $variant = 0) : \PHPStan\Reflection\SignatureMap\FunctionSignature
    {
        $signature = $this->getFunctionSignature(\sprintf('%s::%s', $className, $methodName), $className, $variant);
        $parameters = [];
        foreach ($signature->getParameters() as $i => $parameter) {
            if ($reflectionMethod === null) {
                $parameters[] = $parameter;
                continue;
            }
            $nativeParameters = $reflectionMethod->getParameters();
            if (!\array_key_exists($i, $nativeParameters)) {
                $parameters[] = $parameter;
                continue;
            }
            $parameters[] = new \PHPStan\Reflection\SignatureMap\ParameterSignature($parameter->getName(), $parameter->isOptional(), $parameter->getType(), \PHPStan\Type\TypehintHelper::decideTypeFromReflection($nativeParameters[$i]->getType()), $parameter->passedByReference(), $parameter->isVariadic());
        }
        if ($reflectionMethod === null) {
            $nativeReturnType = new \PHPStan\Type\MixedType();
        } else {
            $nativeReturnType = \PHPStan\Type\TypehintHelper::decideTypeFromReflection($reflectionMethod->getReturnType());
        }
        return new \PHPStan\Reflection\SignatureMap\FunctionSignature($parameters, $signature->getReturnType(), $nativeReturnType, $signature->isVariadic());
    }
    /**
     * @param string|null $className
     */
    public function getFunctionSignature(string $functionName, $className, int $variant = 0) : \PHPStan\Reflection\SignatureMap\FunctionSignature
    {
        $functionName = \strtolower($functionName);
        if ($variant > 0) {
            $functionName .= '\'' . $variant;
        }
        if (!$this->hasFunctionSignature($functionName)) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $signatureMap = self::getSignatureMap();
        return $this->parser->getFunctionSignature($signatureMap[$functionName], $className);
    }
    public function hasMethodMetadata(string $className, string $methodName) : bool
    {
        return $this->hasFunctionMetadata(\sprintf('%s::%s', $className, $methodName));
    }
    public function hasFunctionMetadata(string $name) : bool
    {
        $signatureMap = $this->getFunctionMetadataMap();
        return \array_key_exists(\strtolower($name), $signatureMap);
    }
    /**
     * @param string $className
     * @param string $methodName
     * @return array{hasSideEffects: bool}
     */
    public function getMethodMetadata(string $className, string $methodName) : array
    {
        return $this->getFunctionMetadata(\sprintf('%s::%s', $className, $methodName));
    }
    /**
     * @param string $functionName
     * @return array{hasSideEffects: bool}
     */
    public function getFunctionMetadata(string $functionName) : array
    {
        $functionName = \strtolower($functionName);
        if (!$this->hasFunctionMetadata($functionName)) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return $this->getFunctionMetadataMap()[$functionName];
    }
    /**
     * @return array<string, array{hasSideEffects: bool}>
     */
    private function getFunctionMetadataMap() : array
    {
        if ($this->functionMetadata === null) {
            /** @var array<string, array{hasSideEffects: bool}> $metadata */
            $metadata = (require __DIR__ . '/../../../resources/functionMetadata.php');
            $this->functionMetadata = \array_change_key_case($metadata, \CASE_LOWER);
        }
        return $this->functionMetadata;
    }
    /**
     * @return mixed[]
     */
    public function getSignatureMap() : array
    {
        if ($this->signatureMap === null) {
            $signatureMap = (require __DIR__ . '/../../../resources/functionMap.php');
            if (!\is_array($signatureMap)) {
                throw new \PHPStan\ShouldNotHappenException('Signature map could not be loaded.');
            }
            $signatureMap = \array_change_key_case($signatureMap, \CASE_LOWER);
            if ($this->phpVersion->getVersionId() >= 70400) {
                $php74MapDelta = (require __DIR__ . '/../../../resources/functionMap_php74delta.php');
                if (!\is_array($php74MapDelta)) {
                    throw new \PHPStan\ShouldNotHappenException('Signature map could not be loaded.');
                }
                $signatureMap = $this->computeSignatureMap($signatureMap, $php74MapDelta);
            }
            if ($this->phpVersion->getVersionId() >= 80000) {
                $php80MapDelta = (require __DIR__ . '/../../../resources/functionMap_php80delta.php');
                if (!\is_array($php80MapDelta)) {
                    throw new \PHPStan\ShouldNotHappenException('Signature map could not be loaded.');
                }
                $signatureMap = $this->computeSignatureMap($signatureMap, $php80MapDelta);
            }
            $this->signatureMap = $signatureMap;
        }
        return $this->signatureMap;
    }
    /**
     * @param array<string, mixed> $signatureMap
     * @param array<string, array<string, mixed>> $delta
     * @return array<string, mixed>
     */
    private function computeSignatureMap(array $signatureMap, array $delta) : array
    {
        foreach (\array_keys($delta['old']) as $key) {
            unset($signatureMap[\strtolower($key)]);
        }
        foreach ($delta['new'] as $key => $signature) {
            $signatureMap[\strtolower($key)] = $signature;
        }
        return $signatureMap;
    }
}
