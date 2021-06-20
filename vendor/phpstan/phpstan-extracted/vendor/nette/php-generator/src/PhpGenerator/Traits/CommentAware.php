<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Traits;

/**
 * @internal
 */
trait CommentAware
{
    /** @var string|null */
    private $comment;
    /** @return static
     * @param string|null $val */
    public function setComment($val)
    {
        $this->comment = $val;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }
    /** @return static */
    public function addComment(string $val)
    {
        $this->comment .= $this->comment ? "\n{$val}" : $val;
        return $this;
    }
}
