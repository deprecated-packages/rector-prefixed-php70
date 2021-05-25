<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Class property description.
 *
 * @property mixed $value
 */
final class Property
{
    use Nette\SmartObject;
    use Traits\NameAware;
    use Traits\VisibilityAware;
    use Traits\CommentAware;
    use Traits\AttributeAware;
    /** @var mixed */
    private $value;
    /** @var bool */
    private $static = \false;
    /** @var string|null */
    private $type;
    /** @var bool */
    private $nullable = \false;
    /** @var bool */
    private $initialized = \false;
    /** @return static */
    public function setValue($val)
    {
        $this->value = $val;
        return $this;
    }
    public function &getValue()
    {
        return $this->value;
    }
    /** @return static */
    public function setStatic(bool $state = \true)
    {
        $this->static = $state;
        return $this;
    }
    public function isStatic() : bool
    {
        return $this->static;
    }
    /** @return static
     * @param string|null $val */
    public function setType($val)
    {
        $this->type = $val;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }
    /** @return static */
    public function setNullable(bool $state = \true)
    {
        $this->nullable = $state;
        return $this;
    }
    public function isNullable() : bool
    {
        return $this->nullable;
    }
    /** @return static */
    public function setInitialized(bool $state = \true)
    {
        $this->initialized = $state;
        return $this;
    }
    public function isInitialized() : bool
    {
        return $this->initialized;
    }
}
