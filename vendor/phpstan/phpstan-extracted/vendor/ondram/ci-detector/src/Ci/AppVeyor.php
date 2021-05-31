<?php

declare (strict_types=1);
namespace RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class AppVeyor extends \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('APPVEYOR') === 'True';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_APPVEYOR;
    }
    public function isPullRequest() : \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('APPVEYOR_PULL_REQUEST_NUMBER') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('APPVEYOR_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s/project/%s/%s/builds/%s', $this->env->get('APPVEYOR_URL'), $this->env->get('APPVEYOR_ACCOUNT_NAME'), $this->env->get('APPVEYOR_PROJECT_SLUG'), $this->env->get('APPVEYOR_BUILD_ID'));
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('APPVEYOR_REPO_COMMIT');
    }
    public function getGitBranch() : string
    {
        $prBranch = $this->env->getString('APPVEYOR_PULL_REQUEST_HEAD_REPO_BRANCH');
        if ($this->isPullRequest()->no() || empty($prBranch)) {
            return $this->env->getString('APPVEYOR_REPO_BRANCH');
        }
        return $prBranch;
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('APPVEYOR_REPO_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
