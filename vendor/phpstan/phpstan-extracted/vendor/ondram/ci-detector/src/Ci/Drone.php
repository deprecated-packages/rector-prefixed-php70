<?php

declare (strict_types=1);
namespace RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class Drone extends \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('CI') === 'drone';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_DRONE;
    }
    public function isPullRequest() : \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('DRONE_PULL_REQUEST') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('DRONE_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('DRONE_BUILD_LINK');
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('DRONE_COMMIT_SHA');
    }
    public function getGitBranch() : string
    {
        return $this->env->getString('DRONE_COMMIT_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('DRONE_REPO');
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('DRONE_REPO_LINK');
    }
}
