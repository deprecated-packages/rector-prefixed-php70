<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210620\Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use RectorPrefix20210620\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20210620\Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use RectorPrefix20210620\Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
/**
 * Yields a non-variadic argument's value from the request attributes.
 *
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class RequestAttributeValueResolver implements \RectorPrefix20210620\Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(\RectorPrefix20210620\Symfony\Component\HttpFoundation\Request $request, \RectorPrefix20210620\Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument) : bool
    {
        return !$argument->isVariadic() && $request->attributes->has($argument->getName());
    }
    /**
     * {@inheritdoc}
     * @return mixed[]
     */
    public function resolve(\RectorPrefix20210620\Symfony\Component\HttpFoundation\Request $request, \RectorPrefix20210620\Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument)
    {
        (yield $request->attributes->get($argument->getName()));
    }
}
