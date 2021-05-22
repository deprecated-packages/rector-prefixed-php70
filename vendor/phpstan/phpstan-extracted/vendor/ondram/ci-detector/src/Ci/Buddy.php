<?php

declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class Buddy extends \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('BUDDY') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_BUDDY;
    }
    public function isPullRequest() : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('BUDDY_EXECUTION_PULL_REQUEST_ID') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUDDY_EXECUTION_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('BUDDY_EXECUTION_URL');
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('BUDDY_EXECUTION_REVISION');
    }
    public function getGitBranch() : string
    {
        $prBranch = $this->env->getString('BUDDY_EXECUTION_PULL_REQUEST_HEAD_BRANCH');
        if ($this->isPullRequest()->no() || empty($prBranch)) {
            return $this->env->getString('BUDDY_EXECUTION_BRANCH');
        }
        return $prBranch;
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('BUDDY_REPO_SLUG');
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('BUDDY_SCM_URL');
    }
}
