<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use Closure;
use InvalidArgumentException;
use OutOfBoundsException;
use PhpParser\Node\Stmt\ClassMethod as MethodNode;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\BetterReflection\Reflection\Exception\ClassDoesNotExist;
use PHPStan\BetterReflection\Reflection\Exception\InvalidAbstractFunctionNodeType;
use PHPStan\BetterReflection\Reflection\Exception\NoObjectProvided;
use PHPStan\BetterReflection\Reflection\Exception\NotAnObject;
use PHPStan\BetterReflection\Reflection\Exception\ObjectNotInstanceOfClass;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionMethodStringCast;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use ReflectionException;
use ReflectionMethod as CoreReflectionMethod;
use function class_exists;
use function get_class;
use function is_object;
use function sprintf;
use function strtolower;
class ReflectionMethod extends \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract
{
    /** @var ReflectionClass */
    private $declaringClass;
    /** @var ReflectionClass */
    private $implementingClass;
    /** @var MethodNode */
    private $methodNode;
    /** @var string|null */
    private $aliasName;
    /**
     * @internal
     *
     * @param MethodNode $node Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     *
     * @throws InvalidAbstractFunctionNodeType
     * @return $this
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     * @param string|null $aliasName
     */
    public static function createFromNode(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node\Stmt\ClassMethod $node, $namespace, \PHPStan\BetterReflection\Reflection\ReflectionClass $declaringClass, \PHPStan\BetterReflection\Reflection\ReflectionClass $implementingClass, $aliasName = null)
    {
        $method = new self();
        $method->declaringClass = $declaringClass;
        $method->implementingClass = $implementingClass;
        $method->methodNode = $node;
        $method->aliasName = $aliasName;
        $method->populateFunctionAbstract($reflector, $node, $declaringClass->getLocatedSource(), $namespace);
        return $method;
    }
    /**
     * Create a reflection of a method by it's name using a named class
     *
     * @throws IdentifierNotFound
     * @throws OutOfBoundsException
     * @return $this
     */
    public static function createFromName(string $className, string $methodName)
    {
        return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromName($className)->getMethod($methodName);
    }
    /**
     * Create a reflection of a method by it's name using an instance
     *
     * @param object $instance
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws IdentifierNotFound
     * @throws OutOfBoundsException
     * @return $this
     */
    public static function createFromInstance($instance, string $methodName)
    {
        return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromInstance($instance)->getMethod($methodName);
    }
    public function getShortName() : string
    {
        if ($this->aliasName !== null) {
            return $this->aliasName;
        }
        return parent::getShortName();
    }
    /**
     * @return string|null
     */
    public function getAliasName()
    {
        return $this->aliasName;
    }
    /**
     * Find the prototype for this method, if it exists. If it does not exist
     * it will throw a MethodPrototypeNotFound exception.
     *
     * @throws Exception\MethodPrototypeNotFound
     * @return $this
     */
    public function getPrototype()
    {
        $currentClass = $this->getImplementingClass();
        while ($currentClass) {
            foreach ($currentClass->getImmediateInterfaces() as $interface) {
                if ($interface->hasMethod($this->getName())) {
                    return $interface->getMethod($this->getName());
                }
            }
            $currentClass = $currentClass->getParentClass();
            if ($currentClass === null || !$currentClass->hasMethod($this->getName())) {
                break;
            }
            $prototype = $currentClass->getMethod($this->getName())->findPrototype();
            if ($prototype !== null) {
                if ($this->isConstructor() && !$prototype->isAbstract()) {
                    break;
                }
                return $prototype;
            }
        }
        throw new \PHPStan\BetterReflection\Reflection\Exception\MethodPrototypeNotFound(\sprintf('Method %s::%s does not have a prototype', $this->getDeclaringClass()->getName(), $this->getName()));
    }
    /**
     * @return $this|null
     */
    private function findPrototype()
    {
        if ($this->isAbstract()) {
            return $this;
        }
        if ($this->isPrivate()) {
            return null;
        }
        try {
            return $this->getPrototype();
        } catch (\PHPStan\BetterReflection\Reflection\Exception\MethodPrototypeNotFound $e) {
            return $this;
        }
    }
    /**
     * Get the core-reflection-compatible modifier values.
     */
    public function getModifiers() : int
    {
        $val = 0;
        $val += $this->isStatic() ? \ReflectionMethod::IS_STATIC : 0;
        $val += $this->isPublic() ? \ReflectionMethod::IS_PUBLIC : 0;
        $val += $this->isProtected() ? \ReflectionMethod::IS_PROTECTED : 0;
        $val += $this->isPrivate() ? \ReflectionMethod::IS_PRIVATE : 0;
        $val += $this->isAbstract() ? \ReflectionMethod::IS_ABSTRACT : 0;
        $val += $this->isFinal() ? \ReflectionMethod::IS_FINAL : 0;
        return $val;
    }
    public function __toString() : string
    {
        return \PHPStan\BetterReflection\Reflection\StringCast\ReflectionMethodStringCast::toString($this);
    }
    public function inNamespace() : bool
    {
        return \false;
    }
    /**
     * Is the method abstract.
     */
    public function isAbstract() : bool
    {
        return $this->methodNode->isAbstract() || $this->declaringClass->isInterface();
    }
    /**
     * Is the method final.
     */
    public function isFinal() : bool
    {
        return $this->methodNode->isFinal();
    }
    /**
     * Is the method private visibility.
     */
    public function isPrivate() : bool
    {
        return $this->methodNode->isPrivate();
    }
    /**
     * Is the method protected visibility.
     */
    public function isProtected() : bool
    {
        return $this->methodNode->isProtected();
    }
    /**
     * Is the method public visibility.
     */
    public function isPublic() : bool
    {
        return $this->methodNode->isPublic();
    }
    /**
     * Is the method static.
     */
    public function isStatic() : bool
    {
        return $this->methodNode->isStatic();
    }
    /**
     * Is the method a constructor.
     */
    public function isConstructor() : bool
    {
        if (\strtolower($this->getName()) === '__construct') {
            return \true;
        }
        $declaringClass = $this->getDeclaringClass();
        if ($declaringClass->inNamespace()) {
            return \false;
        }
        return \strtolower($this->getName()) === \strtolower($declaringClass->getShortName());
    }
    /**
     * Is the method a destructor.
     */
    public function isDestructor() : bool
    {
        return \strtolower($this->getName()) === '__destruct';
    }
    /**
     * Get the class that declares this method.
     */
    public function getDeclaringClass() : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        return $this->declaringClass;
    }
    public function getImplementingClass() : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        return $this->implementingClass;
    }
    public function isInternal() : bool
    {
        return $this->declaringClass->getLocatedSource()->isInternal();
    }
    /**
     * @param object|null $object
     *
     * @throws ClassDoesNotExist
     * @throws NoObjectProvided
     * @throws NotAnObject
     * @throws ObjectNotInstanceOfClass
     */
    public function getClosure($object = null) : \Closure
    {
        $declaringClassName = $this->getDeclaringClass()->getName();
        if ($this->isStatic()) {
            $this->assertClassExist($declaringClassName);
            return function (...$args) {
                return $this->callStaticMethod($args);
            };
        }
        $instance = $this->assertObject($object);
        return function (...$args) use($instance) {
            return $this->callObjectMethod($instance, $args);
        };
    }
    /**
     * @param object|null $object
     * @param mixed       ...$args
     *
     * @return mixed
     *
     * @throws ClassDoesNotExist
     * @throws NoObjectProvided
     * @throws NotAnObject
     * @throws ObjectNotInstanceOfClass
     */
    public function invoke($object = null, ...$args)
    {
        return $this->invokeArgs($object, $args);
    }
    /**
     * @param object|null $object
     * @param mixed[]     $args
     *
     * @return mixed
     *
     * @throws ClassDoesNotExist
     * @throws NoObjectProvided
     * @throws NotAnObject
     * @throws ObjectNotInstanceOfClass
     */
    public function invokeArgs($object = null, array $args = [])
    {
        $declaringClassName = $this->getDeclaringClass()->getName();
        if ($this->isStatic()) {
            $this->assertClassExist($declaringClassName);
            return $this->callStaticMethod($args);
        }
        return $this->callObjectMethod($this->assertObject($object), $args);
    }
    /**
     * @param mixed[] $args
     *
     * @return mixed
     */
    private function callStaticMethod(array $args)
    {
        $declaringClassName = $this->getDeclaringClass()->getName();
        return \Closure::bind(function (string $declaringClassName, string $methodName, array $methodArgs) {
            return $declaringClassName::$methodName(...$methodArgs);
        }, null, $declaringClassName)->__invoke($declaringClassName, $this->getName(), $args);
    }
    /**
     * @param object  $object
     * @param mixed[] $args
     *
     * @return mixed
     */
    private function callObjectMethod($object, array $args)
    {
        return \Closure::bind(function ($object, string $methodName, array $methodArgs) {
            return $object->{$methodName}(...$methodArgs);
        }, $object, $this->getDeclaringClass()->getName())->__invoke($object, $this->getName(), $args);
    }
    /**
     * @throws ClassDoesNotExist
     * @return void
     */
    private function assertClassExist(string $className)
    {
        if (!\class_exists($className, \false)) {
            throw new \PHPStan\BetterReflection\Reflection\Exception\ClassDoesNotExist(\sprintf('Method of class %s cannot be used as the class is not loaded', $className));
        }
    }
    /**
     * @param mixed $object
     *
     * @return object
     *
     * @throws NoObjectProvided
     * @throws NotAnObject
     * @throws ObjectNotInstanceOfClass
     */
    private function assertObject($object)
    {
        if ($object === null) {
            throw \PHPStan\BetterReflection\Reflection\Exception\NoObjectProvided::create();
        }
        if (!\is_object($object)) {
            throw \PHPStan\BetterReflection\Reflection\Exception\NotAnObject::fromNonObject($object);
        }
        $declaringClassName = $this->getDeclaringClass()->getName();
        if (\get_class($object) !== $declaringClassName) {
            throw \PHPStan\BetterReflection\Reflection\Exception\ObjectNotInstanceOfClass::fromClassName($declaringClassName);
        }
        return $object;
    }
}
