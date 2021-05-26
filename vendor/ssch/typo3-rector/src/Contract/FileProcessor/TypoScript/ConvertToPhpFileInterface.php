<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
interface ConvertToPhpFileInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @return \Rector\FileSystemRector\ValueObject\AddedFileWithContent|null
     */
    public function convert();
    public function getMessage() : string;
}
