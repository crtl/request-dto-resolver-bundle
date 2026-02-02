<?php

/*
 * This file is part of a private project.
 *
 * Copyright 2026 Crtl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crtl\RequestDtoResolverBundle\Test\Unit;

use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class DtoInstanceBagTest extends TestCase
{
    private DtoInstanceBag $bag;

    private Request $request;

    protected function setUp(): void
    {
        $this->bag = new DtoInstanceBag();
        $this->request = new Request();
    }

    public function testRegisterInstanceRegistersInstanceInRequestAttributes(): void
    {
        $instance = new \stdClass();

        $this->bag->registerInstance($instance, $this->request);

        $this->assertTrue($this->request->attributes->has(DtoInstanceBag::DTO_INSTANCES_ATTRIBUTE_KEY));
        $this->assertEquals(
            [get_class($instance) => $instance],
            $this->request->attributes->get(DtoInstanceBag::DTO_INSTANCES_ATTRIBUTE_KEY),
        );
    }

    public function testHasRegisteredInstanceReturnsTrueIfInstanceIsRegistered(): void
    {
        $instance = new \stdClass();

        $this->assertFalse($this->bag->hasRegisteredInstance(get_class($instance), $this->request));

        $this->bag->registerInstance($instance, $this->request);

        $this->assertTrue($this->bag->hasRegisteredInstance(get_class($instance), $this->request));
    }

    public function testGetRegisteredInstancesReturnsAllRegisteredInstances(): void
    {
        $instance1 = new \stdClass();
        $instance2 = new class {};

        $this->assertEmpty($this->bag->getRegisteredInstances($this->request));

        $this->bag->registerInstance($instance1, $this->request);
        $this->bag->registerInstance($instance2, $this->request);

        $instances = $this->bag->getRegisteredInstances($this->request);
        $this->assertCount(2, $instances);
        $this->assertSame($instance1, $instances[get_class($instance1)]);
        $this->assertSame($instance2, $instances[get_class($instance2)]);
    }

    public function testGetRegisteredInstanceReturnsInstanceOrNullIfNotFound(): void
    {
        $instance = new \stdClass();

        $this->assertNull($this->bag->getRegisteredInstance(get_class($instance), $this->request));

        $this->bag->registerInstance($instance, $this->request);

        $this->assertSame($instance, $this->bag->getRegisteredInstance(get_class($instance), $this->request));
    }
}
