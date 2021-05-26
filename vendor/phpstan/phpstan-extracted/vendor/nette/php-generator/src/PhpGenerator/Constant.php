<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Class constant.
 */
final class Constant
{
    use Nette\SmartObject;
    use Traits\NameAware;
    use Traits\VisibilityAware;
    use Traits\CommentAware;
    use Traits\AttributeAware;
    /** @var mixed */
    private $value;
    /** @return static */
    public function setValue($val)
    {
        $this->value = $val;
        return $this;
    }
    public function getValue()
    {
        return $this->value;
    }
}
