<?php

namespace Crtl\RequestDtoResolverBundle\Test\Unit;

use Crtl\RequestDtoResolverBundle\Configuration;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{

    public function testGetters(): void
    {
        $config = new Configuration(true, false);
        self::assertTrue($config->getDefaultStrict());
        self::assertFalse($config->getDefaultNull());

        $config = new Configuration(false, true);
        self::assertFalse($config->getDefaultStrict());
        self::assertTrue($config->getDefaultNull());
    }

}