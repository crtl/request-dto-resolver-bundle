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

use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class HeaderParamTest extends TestCase
{
    public function testGetValueFromRequestReturnsHeaderValueFromRequestHeadersBag(): void
    {
        $paramName = 'test_header';
        $paramValue = 'test_value';

        $request = new Request(server: ['HTTP_'.strtoupper($paramName) => $paramValue]);

        $headerParam = new HeaderParam($paramName);

        $this->assertEquals($paramValue, $headerParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfHeaderIsMissing(): void
    {
        $paramName = 'missing_header';

        $request = new Request();

        $headerParam = new HeaderParam($paramName);

        $this->assertNull($headerParam->getValueFromRequest($request));
    }

    public function testHasValueInRequestReturnsWhetherValueIsExistsInRequest(): void
    {
        $paramName = 'test_header';
        $paramValue = 'test_value';

        $request = new Request(server: ['HTTP_'.strtoupper($paramName) => $paramValue]);

        $param = new HeaderParam($paramName);

        $this->assertTrue($param->hasValueInRequest($request));
        $this->assertFalse($param->hasValueInRequest(new Request()));
    }
}
