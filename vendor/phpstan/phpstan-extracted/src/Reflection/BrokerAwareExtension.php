<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;
interface BrokerAwareExtension
{
    /**
     * @return void
     */
    public function setBroker(\PHPStan\Broker\Broker $broker);
}
