<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BodyParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): ?string
    {
        return $request->request->get($this->name);
    }
}