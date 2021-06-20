<?php

declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci;

use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\CiDetector;
use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env;
use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic;
class TeamCity extends \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('TEAMCITY_VERSION') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\CiDetector::CI_TEAMCITY;
    }
    public function isPullRequest() : \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return '';
        // unsupported
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('BUILD_VCS_NUMBER');
    }
    public function getGitBranch() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryName() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
