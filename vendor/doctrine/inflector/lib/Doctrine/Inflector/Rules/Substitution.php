<?php

declare (strict_types=1);
namespace RectorPrefix20210528\Doctrine\Inflector\Rules;

final class Substitution
{
    /** @var Word */
    private $from;
    /** @var Word */
    private $to;
    public function __construct(\RectorPrefix20210528\Doctrine\Inflector\Rules\Word $from, \RectorPrefix20210528\Doctrine\Inflector\Rules\Word $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    public function getFrom() : \RectorPrefix20210528\Doctrine\Inflector\Rules\Word
    {
        return $this->from;
    }
    public function getTo() : \RectorPrefix20210528\Doctrine\Inflector\Rules\Word
    {
        return $this->to;
    }
}
