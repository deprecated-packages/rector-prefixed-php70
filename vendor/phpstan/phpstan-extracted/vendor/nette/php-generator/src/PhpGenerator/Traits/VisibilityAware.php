<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\Traits;

use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType;
/**
 * @internal
 */
trait VisibilityAware
{
    /** @var string|null  public|protected|private */
    private $visibility;
    /**
     * @param  string|null  $val  public|protected|private
     * @return static
     */
    public function setVisibility($val)
    {
        if (!\in_array($val, [\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PUBLIC, \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PROTECTED, \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PRIVATE, null], \true)) {
            throw new \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\InvalidArgumentException('Argument must be public|protected|private.');
        }
        $this->visibility = $val;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
    /** @return static */
    public function setPublic()
    {
        $this->visibility = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PUBLIC;
        return $this;
    }
    public function isPublic() : bool
    {
        return $this->visibility === \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PUBLIC || $this->visibility === null;
    }
    /** @return static */
    public function setProtected()
    {
        $this->visibility = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PROTECTED;
        return $this;
    }
    public function isProtected() : bool
    {
        return $this->visibility === \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PROTECTED;
    }
    /** @return static */
    public function setPrivate()
    {
        $this->visibility = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PRIVATE;
        return $this;
    }
    public function isPrivate() : bool
    {
        return $this->visibility === \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType::VISIBILITY_PRIVATE;
    }
}
