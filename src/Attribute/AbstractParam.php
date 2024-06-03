<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractParam
{
    public function __construct(
        public ?string $name = null,
    ) {
    }

    /**
     * Retrieves the value from the request and returns it or null if no values was found
     *
     * @param Request $request
     * @return mixed
     */
    abstract public function getValueFromRequest(Request $request): mixed;
}
