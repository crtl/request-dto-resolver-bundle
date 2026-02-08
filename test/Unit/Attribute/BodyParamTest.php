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

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BodyParamTest extends TestCase
{
    public function testGetValueFromRequestReturnsValueFromRequestRequestBag(): void
    {
        $paramName = 'test_param';
        $paramValue = 'test_value';

        $request = new Request([], [$paramName => $paramValue]);

        $bodyParam = new BodyParam($paramName);

        $this->assertEquals($paramValue, $bodyParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfParameterIsMissing(): void
    {
        $request = new Request();
        $bodyParam = new BodyParam('missing_param');

        $this->assertNull($bodyParam->getValueFromRequest($request));
    }

    public function testGetNestedArrayValue(): void
    {
        $bodyParam = new BodyParam('name');
        $parent = new BodyParam('children');
        $parent->setIndex(0);
        $bodyParam->setParent($parent);

        $request = new Request(request: [
            'children' => [
                ['name' => 'John Doe']
            ]
        ]);

        $value = $bodyParam->getValueFromRequest($request);
        self::assertSame('John Doe', $value);
    }

    public function testReadsValueFromJsonRequest(): void
    {
        $request = new Request(
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['name' => 'John Doe'], flags: JSON_THROW_ON_ERROR),
        );

        $bodyParam = new BodyParam('name');
        $value = $bodyParam->getValueFromRequest($request);

        self::assertSame('John Doe', $value);
    }

    public function testHasValueInRequestReturnsWhetherValueIsExistsInRequest(): void
    {
        $request = new Request(request: ["param" => "value"]);

        $param = new BodyParam("param");

        $this->assertTrue($param->hasValueInRequest($request));
        $this->assertFalse($param->hasValueInRequest(new Request()));
    }

    public function testHasValueInRequestWithNestedParam(): void
    {
        $parent = new BodyParam('parent');
        $child = new BodyParam('child');
        $request = new Request(request: [
            "parent" => [
                "child" => "value",
            ]
        ]);

        $child->setParent($parent);

        $this->assertTrue($child->hasValueInRequest($request));
        $this->assertFalse($child->hasValueInRequest(new Request()));
    }

    public function testHasValueInRequestWithNestedArrayParam(): void
    {
        $parent = new BodyParam('parent');
        $child = new BodyParam('child');
        $request = new Request(request: [
            "parent" => [
                ["child" => "value",]
            ]
        ]);

        $parent->setIndex(0);
        $child->setParent($parent);

        $this->assertTrue($child->hasValueInRequest($request));
        $this->assertFalse($child->hasValueInRequest(new Request()));
    }
}
