<?php

declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder;

final class HmacEncoder implements \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface
{
    /**
     * @var string
     */
    private $hmacKey;
    public function __construct(string $hmacKey)
    {
        $this->hmacKey = $hmacKey;
    }
    /**
     * {@inheritDoc}
     */
    public function encode(string $codeWithoutSignature) : string
    {
        return \hash_hmac('sha256', $codeWithoutSignature, $this->hmacKey);
    }
    /**
     * {@inheritDoc}
     */
    public function verify(string $codeWithoutSignature, string $signature) : bool
    {
        return \hash_equals($this->encode($codeWithoutSignature), $signature);
    }
}
