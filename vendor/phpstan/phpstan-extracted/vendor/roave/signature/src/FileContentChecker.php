<?php

declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface;
final class FileContentChecker implements \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature\CheckerInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;
    /**
     * {@inheritDoc}
     */
    public function __construct(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    /**
     * {@inheritDoc}
     */
    public function check(string $phpCode) : bool
    {
        if (!\preg_match('{Roave/Signature:\\s+([a-zA-Z0-9\\/=]+)}', $phpCode, $matches)) {
            return \false;
        }
        return $this->encoder->verify($this->stripCodeSignature($phpCode), $matches[1]);
    }
    /**
     * @param string $phpCode
     *
     * @return string
     */
    private function stripCodeSignature(string $phpCode) : string
    {
        return \preg_replace('{[\\/\\*\\s]+Roave/Signature:\\s+([a-zA-Z0-9\\/\\*\\/ =]+)}', '', $phpCode);
    }
}
