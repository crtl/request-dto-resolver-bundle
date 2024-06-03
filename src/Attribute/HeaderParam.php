<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$headers}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class HeaderParam extends AbstractParam
{

    /**
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): ?string
    {
        return $request->headers->get($this->getName());
    }

}
