<?php

declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Roave\Signature;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface;
final class FileContentSigner implements \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Roave\Signature\SignerInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;
    /**
     * {@inheritDoc}
     */
    public function __construct(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function sign(string $phpCode) : string
    {
        return 'Roave/Signature: ' . $this->encoder->encode($phpCode);
    }
}
