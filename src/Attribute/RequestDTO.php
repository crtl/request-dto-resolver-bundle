<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Crtl\RequestDTOResolverBundle\RequestDTOResolver;

/**
 * Marks a class as request DTO which will be resolved and validated
 * by {@link RequestDTOResolver} for controller action arguments
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RequestDTO
{

}
