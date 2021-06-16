<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Reflection\ClassReflection;
class ScopeContext
{
    /** @var string */
    private $file;
    /** @var ClassReflection|null */
    private $classReflection;
    /** @var ClassReflection|null */
    private $traitReflection;
    /**
     * @param \PHPStan\Reflection\ClassReflection|null $classReflection
     * @param \PHPStan\Reflection\ClassReflection|null $traitReflection
     */
    private function __construct(string $file, $classReflection, $traitReflection)
    {
        $this->file = $file;
        $this->classReflection = $classReflection;
        $this->traitReflection = $traitReflection;
    }
    /** @api
     * @return $this */
    public static function create(string $file)
    {
        return new self($file, null, null);
    }
    /**
     * @return $this
     */
    public function beginFile()
    {
        return new self($this->file, null, null);
    }
    /**
     * @return $this
     */
    public function enterClass(\PHPStan\Reflection\ClassReflection $classReflection)
    {
        if ($this->classReflection !== null && !$classReflection->isAnonymous()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        if ($classReflection->isTrait()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return new self($this->file, $classReflection, null);
    }
    /**
     * @return $this
     */
    public function enterTrait(\PHPStan\Reflection\ClassReflection $traitReflection)
    {
        if ($this->classReflection === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        if (!$traitReflection->isTrait()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return new self($this->file, $this->classReflection, $traitReflection);
    }
    /**
     * @param $this $otherContext
     */
    public function equals($otherContext) : bool
    {
        if ($this->file !== $otherContext->file) {
            return \false;
        }
        if ($this->getClassReflection() === null) {
            return $otherContext->getClassReflection() === null;
        } elseif ($otherContext->getClassReflection() === null) {
            return \false;
        }
        $isSameClass = $this->getClassReflection()->getName() === $otherContext->getClassReflection()->getName();
        if ($this->getTraitReflection() === null) {
            return $otherContext->getTraitReflection() === null && $isSameClass;
        } elseif ($otherContext->getTraitReflection() === null) {
            return \false;
        }
        $isSameTrait = $this->getTraitReflection()->getName() === $otherContext->getTraitReflection()->getName();
        return $isSameClass && $isSameTrait;
    }
    public function getFile() : string
    {
        return $this->file;
    }
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getClassReflection()
    {
        return $this->classReflection;
    }
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getTraitReflection()
    {
        return $this->traitReflection;
    }
}
