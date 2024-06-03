<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$request}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BodyParam extends AbstractParam
{

    /**
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): ?string
    {
        return $request->request->get($this->getName());
    }
}
