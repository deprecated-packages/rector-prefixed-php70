<?php

declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\CiInterface;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Exception\CiNotDetectedException;
/**
 * Unified way to get environment variables from current continuous integration server
 */
class CiDetector
{
    const CI_APPVEYOR = 'AppVeyor';
    const CI_AWS_CODEBUILD = 'AWS CodeBuild';
    const CI_BAMBOO = 'Bamboo';
    const CI_BITBUCKET_PIPELINES = 'Bitbucket Pipelines';
    const CI_BUDDY = 'Buddy';
    const CI_CIRCLE = 'CircleCI';
    const CI_CODESHIP = 'Codeship';
    const CI_CONTINUOUSPHP = 'continuousphp';
    const CI_DRONE = 'drone';
    const CI_GITHUB_ACTIONS = 'GitHub Actions';
    const CI_GITLAB = 'GitLab';
    const CI_JENKINS = 'Jenkins';
    const CI_TEAMCITY = 'TeamCity';
    const CI_TRAVIS = 'Travis CI';
    const CI_WERCKER = 'Wercker';
    /** @var Env */
    private $environment;
    public function __construct()
    {
        $this->environment = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env();
    }
    /**
     * @return $this
     */
    public static function fromEnvironment(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $environment)
    {
        $detector = new static();
        $detector->environment = $environment;
        return $detector;
    }
    /**
     * Is current environment an recognized CI server?
     */
    public function isCiDetected() : bool
    {
        $ciServer = $this->detectCurrentCiServer();
        return $ciServer !== null;
    }
    /**
     * Detect current CI server and return instance of its settings
     *
     * @throws CiNotDetectedException
     */
    public function detect() : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\CiInterface
    {
        $ciServer = $this->detectCurrentCiServer();
        if ($ciServer === null) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Exception\CiNotDetectedException('No CI server detected in current environment');
        }
        return $ciServer;
    }
    /**
     * @return string[]
     */
    protected function getCiServers() : array
    {
        return [\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AppVeyor::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\AwsCodeBuild::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Bamboo::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\BitbucketPipelines::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Buddy::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Circle::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Codeship::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Continuousphp::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Drone::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\GitHubActions::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\GitLab::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Jenkins::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\TeamCity::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Travis::class, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\Wercker::class];
    }
    /**
     * @return \_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\CiInterface|null
     */
    protected function detectCurrentCiServer()
    {
        $ciServers = $this->getCiServers();
        foreach ($ciServers as $ciClass) {
            $callback = [$ciClass, 'isDetected'];
            if (\is_callable($callback)) {
                if ($callback($this->environment)) {
                    return new $ciClass($this->environment);
                }
            }
        }
        return null;
    }
}
