<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$query}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class QueryParam extends AbstractNestedParam
{
    protected function getInputBag(Request $request): InputBag
    {
        return $request->query;
    }

}
