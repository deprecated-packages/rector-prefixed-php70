<?php

declare (strict_types=1);
namespace RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class Travis extends \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    const TRAVIS_BASE_URL = 'https://travis-ci.org';
    public static function isDetected(\RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('TRAVIS') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_TRAVIS;
    }
    public function isPullRequest() : \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('TRAVIS_PULL_REQUEST') !== 'false');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('TRAVIS_JOB_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s/%s/jobs/%s', self::TRAVIS_BASE_URL, $this->env->get('TRAVIS_REPO_SLUG'), $this->env->get('TRAVIS_JOB_ID'));
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('TRAVIS_COMMIT');
    }
    public function getGitBranch() : string
    {
        if ($this->isPullRequest()->no()) {
            return $this->env->getString('TRAVIS_BRANCH');
        }
        // If the build is for PR, return name of the branch with the PR, not the target PR branch
        // https://github.com/travis-ci/travis-ci/issues/6652
        return $this->env->getString('TRAVIS_PULL_REQUEST_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('TRAVIS_REPO_SLUG');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
