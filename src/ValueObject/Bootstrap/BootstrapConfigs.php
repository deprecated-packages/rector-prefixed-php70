<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;
final class BootstrapConfigs
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private $mainConfigFileInfo;
    /**
     * @var mixed[]
     */
    private $setConfigFileInfos;
    /**
     * @param SmartFileInfo[] $setConfigFileInfos
     * @param \Symplify\SmartFileSystem\SmartFileInfo|null $mainConfigFileInfo
     */
    public function __construct($mainConfigFileInfo, array $setConfigFileInfos)
    {
        $this->mainConfigFileInfo = $mainConfigFileInfo;
        $this->setConfigFileInfos = $setConfigFileInfos;
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    public function getMainConfigFileInfo()
    {
        return $this->mainConfigFileInfo;
    }
    /**
     * @return SmartFileInfo[]
     */
    public function getConfigFileInfos() : array
    {
        $configFileInfos = [];
        if ($this->mainConfigFileInfo !== null) {
            $configFileInfos[] = $this->mainConfigFileInfo;
        }
        return \array_merge($configFileInfos, $this->setConfigFileInfos);
    }
}
