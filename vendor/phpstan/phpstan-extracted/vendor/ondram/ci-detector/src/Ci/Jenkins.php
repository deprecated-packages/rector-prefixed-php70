<?php

declare (strict_types=1);
namespace RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class Jenkins extends \RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('JENKINS_URL') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_JENKINS;
    }
    public function isPullRequest() : \RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('BUILD_URL');
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('GIT_COMMIT');
    }
    public function getGitBranch() : string
    {
        return $this->env->getString('GIT_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('GIT_URL');
    }
}
