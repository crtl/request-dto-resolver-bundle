<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RouteParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): mixed
    {
        $routeParams = $request->attributes->get("_route_params") ?? [];
        return $routeParams[$this->name] ?? null;
    }


}
