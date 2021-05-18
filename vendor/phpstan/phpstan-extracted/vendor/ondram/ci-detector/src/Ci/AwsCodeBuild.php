<?php

declare (strict_types=1);
namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic;
class AwsCodeBuild extends \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('CODEBUILD_CI') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\CiDetector::CI_AWS_CODEBUILD;
    }
    public function isPullRequest() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\TrinaryLogic::createFromBoolean(\mb_strpos($this->env->getString('CODEBUILD_WEBHOOK_EVENT'), 'PULL_REQUEST') === 0);
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CODEBUILD_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CODEBUILD_BUILD_URL');
    }
    public function getGitCommit() : string
    {
        return $this->env->getString('CODEBUILD_RESOLVED_SOURCE_VERSION');
    }
    public function getGitBranch() : string
    {
        $gitReference = $this->env->getString('CODEBUILD_WEBHOOK_HEAD_REF');
        return \preg_replace('~^refs/heads/~', '', $gitReference) ?? '';
    }
    public function getRepositoryName() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('CODEBUILD_SOURCE_REPO_URL');
    }
}
