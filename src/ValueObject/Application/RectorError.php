<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorError
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $fileInfo;
    /**
     * @var string
     */
    private $message;
    /**
     * @var int|null
     */
    private $line;
    /**
     * @var string|null
     */
    private $rectorClass;
    /**
     * @param int|null $line
     * @param string|null $rectorClass
     */
    public function __construct(string $message, $line = null, $rectorClass = null)
    {
        $this->message = $message;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }
    public function getRelativeFilePath() : string
    {
        return $this->fileInfo->getRelativeFilePathFromCwd();
    }
    public function getFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->fileInfo;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    /**
     * @return int|null
     */
    public function getLine()
    {
        return $this->line;
    }
    /**
     * @return string|null
     */
    public function getRectorClass()
    {
        return $this->rectorClass;
    }
}
