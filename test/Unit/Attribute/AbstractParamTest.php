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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class TestClass
{
    public string $testProperty;
}

final class TestParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): mixed
    {
        return $request->request->get($this->getName());
    }
}

final class AbstractParamTest extends TestCase
{
    public function testGetNameReturnsExplicitNameIfSet(): void
    {
        $param = new TestParam('testName');

        $this->assertEquals('testName', $param->getName());
    }

    public function testGetNameReturnsPropertyNameIfNoExplicitNameIsSet(): void
    {
        $param = new TestParam();

        $property = new \ReflectionProperty(TestClass::class, 'testProperty');
        $param->setProperty($property);

        $this->assertEquals('testProperty', $param->getName());
    }

    public function testGetNameThrowsLogicExceptionIfNeitherNameNorPropertyIsSet(): void
    {
        $this->expectException(\LogicException::class);

        $param = new TestParam();

        $param->getName();
    }

    public function testSetPropertyCorrectlySetsThePropertyInstance(): void
    {
        $param = new TestParam();

        $property = new \ReflectionProperty(TestClass::class, 'testProperty');
        $param->setProperty($property);

        $this->assertSame($property, $param->getProperty());
    }

    public function testGetValueFromRequestReturnsValueFromRequestRequestBag(): void
    {
        $request = new Request([], ['param' => 'value']);

        $param = new TestParam('param');

        $this->assertEquals('value', $param->getValueFromRequest($request));
    }
}
