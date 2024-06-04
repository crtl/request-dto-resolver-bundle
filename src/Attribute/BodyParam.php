<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$request}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BodyParam extends AbstractNestedParam
{
    protected function getInputBag(Request $request): InputBag
    {
        return $request->request;
    }

}
