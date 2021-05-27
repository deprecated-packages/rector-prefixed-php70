<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Function/Method parameter description.
 *
 * @property mixed $defaultValue
 */
class Parameter
{
    use Nette\SmartObject;
    use Traits\NameAware;
    use Traits\AttributeAware;
    /** @var bool */
    private $reference = \false;
    /** @var string|null */
    private $type;
    /** @var bool */
    private $nullable = \false;
    /** @var bool */
    private $hasDefaultValue = \false;
    /** @var mixed */
    private $defaultValue;
    /** @return static */
    public function setReference(bool $state = \true)
    {
        $this->reference = $state;
        return $this;
    }
    public function isReference() : bool
    {
        return $this->reference;
    }
    /** @return static
     * @param string|null $type */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }
    /** @deprecated  use setType()
     * @return $this
     * @param string|null $type */
    public function setTypeHint($type)
    {
        $this->type = $type;
        return $this;
    }
    /** @deprecated  use getType()
     * @return string|null */
    public function getTypeHint()
    {
        return $this->type;
    }
    /**
     * @deprecated  just use setDefaultValue()
     * @return static
     */
    public function setOptional(bool $state = \true)
    {
        \trigger_error(__METHOD__ . '() is deprecated, use setDefaultValue()', \E_USER_DEPRECATED);
        $this->hasDefaultValue = $state;
        return $this;
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
    public function setDefaultValue($val)
    {
        $this->defaultValue = $val;
        $this->hasDefaultValue = \true;
        return $this;
    }
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    public function hasDefaultValue() : bool
    {
        return $this->hasDefaultValue;
    }
}
