<?php

declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci;

use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\CiDetector;
use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env;
use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic;
class Wercker extends \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('WERCKER') === 'true';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\CiDetector::CI_WERCKER;
    }
    public function isPullRequest() : \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('WERCKER_RUN_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('WERCKER_RUN_URL');
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('WERCKER_GIT_COMMIT');
    }
    public function getGitBranch() : string
    {
        return $this->env->getString('WERCKER_GIT_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('WERCKER_GIT_OWNER') . '/' . $this->env->getString('WERCKER_GIT_REPOSITORY');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
