<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from requests route params
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class RouteParam extends AbstractParam
{

    /**
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): mixed
    {
        $routeParams = $request->attributes->get("_route_params") ?? [];
        return $routeParams[$this->getName()] ?? null;
    }


}
