<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */
namespace RectorPrefix20210620\Hoa\Iterator\Recursive;

/**
 * Class \Hoa\Iterator\Recursive\Directory.
 *
 * Extending the SPL RecursiveDirectoryIterator class.
 *
 * @copyright  Copyright © 2007-2017 Hoa community
 * @license    New BSD License
 */
class Directory extends \RecursiveDirectoryIterator
{
    /**
     * SplFileInfo classname.
     *
     * @var string
     */
    protected $_splFileInfoClass = null;
    /**
     * Relative path.
     *
     * @var string
     */
    protected $_relativePath = 0;
    /**
     * Workaround for the bug #65136.
     *
     * @var string
     */
    private static $_handlePath = null;
    /**
     * Constructor.
     * Please, see \RecursiveDirectoryIterator::__construct() method.
     * We add the $splFileInfoClass parameter.
     *
     * @param   string  $path                Path.
     * @param   int     $flags               Flags.
     * @param   string  $splFileInfoClass    SplFileInfo classname.
     */
    public function __construct($path, $flags = null, $splFileInfoClass = null)
    {
        if (null === $flags) {
            parent::__construct($path);
        } else {
            parent::__construct($path, $flags);
        }
        if (null !== self::$_handlePath) {
            $this->_relativePath = self::$_handlePath;
            self::$_handlePath = null;
        } else {
            $this->_relativePath = $path;
        }
        $this->setSplFileInfoClass($splFileInfoClass);
        return;
    }
    /**
     * Current.
     * Please, see \RecursiveDirectoryIterator::current() method.
     *
     * @return  mixed
     */
    public function current()
    {
        $out = parent::current();
        if (null !== $this->_splFileInfoClass && $out instanceof \SplFileInfo) {
            $out->setInfoClass($this->_splFileInfoClass);
            $out = $out->getFileInfo();
            if ($out instanceof \RectorPrefix20210620\Hoa\Iterator\SplFileInfo) {
                $out->setRelativePath($this->getRelativePath());
            }
        }
        return $out;
    }
    /**
     * Get children.
     * Please, see \RecursiveDirectoryIterator::getChildren() method.
     *
     * @return  mixed
     */
    public function getChildren()
    {
        self::$_handlePath = $this->getRelativePath();
        $out = parent::getChildren();
        if ($out instanceof \RecursiveDirectoryIterator) {
            $out->setSplFileInfoClass($this->_splFileInfoClass);
        }
        return $out;
    }
    /**
     * Set SplFileInfo classname.
     *
     * @param   string  $splFileInfoClass    SplFileInfo classname.
     * @return  void
     */
    public function setSplFileInfoClass($splFileInfoClass)
    {
        $this->_splFileInfoClass = $splFileInfoClass;
        return;
    }
    /**
     * Get relative path (if given).
     *
     * @return  string
     */
    public function getRelativePath()
    {
        return $this->_relativePath;
    }
}
