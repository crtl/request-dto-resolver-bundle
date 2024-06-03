<?php

namespace Crtl\RequestDTOResolverBundle\Test\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RouteParamTest extends TestCase
{
    public function testGetValueFromRequest()
    {
        $paramName = "test_route";
        $paramValue = "test_value";

        $request = new Request([], [], ["_route_params" => [$paramName => $paramValue]]);

        $routeParam = new RouteParam($paramName);

        $this->assertEquals($paramValue, $routeParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithMissingRouteParam()
    {
        $paramName = "missing_route";

        $request = new Request([], [], ["_route_params" => []]);

        $routeParam = new RouteParam($paramName);

        $this->assertNull($routeParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithNoRouteParams()
    {
        $paramName = "missing_route";

        $request = new Request();

        $routeParam = new RouteParam($paramName);

        $this->assertNull($routeParam->getValueFromRequest($request));
    }
}
