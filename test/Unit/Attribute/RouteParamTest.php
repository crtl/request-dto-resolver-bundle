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

use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class RouteParamTest extends TestCase
{
    public function testGetValueFromRequestReturnsRouteParameterFromRequestAttributesBag(): void
    {
        $paramName = 'test_route';
        $paramValue = 'test_value';

        $request = new Request(attributes: ['_route_params' => [$paramName => $paramValue]]);

        $routeParam = new RouteParam($paramName);

        $this->assertEquals($paramValue, $routeParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfRouteParameterIsMissing(): void
    {
        $paramName = 'missing_route';

        $request = new Request(attributes: ['_route_params' => []]);

        $routeParam = new RouteParam($paramName);

        $this->assertNull($routeParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfNoRouteParametersArePresent(): void
    {
        $paramName = 'missing_route';

        $request = new Request();

        $routeParam = new RouteParam($paramName);

        $this->assertNull($routeParam->getValueFromRequest($request));
    }

    public function testHasValueInRequestReturnsWhetherValueIsExistsInRequest(): void
    {
        $paramName = 'test_route';
        $paramValue = 'test_value';

        $request = new Request(attributes: ['_route_params' => [$paramName => $paramValue]]);

        $param = new RouteParam($paramName);

        $this->assertTrue($param->hasValueInRequest($request));
        $this->assertFalse($param->hasValueInRequest(new Request()));
    }
}
