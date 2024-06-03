<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$query}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class QueryParam extends AbstractParam
{

    /**
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): ?string
    {
        return $request->query->get($this->getName());
    }

}
