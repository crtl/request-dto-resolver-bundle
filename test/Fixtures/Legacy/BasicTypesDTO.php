<?php

/*
 * This file is part of a private project.
 *
 * Copyright 2026 Crtl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures\Legacy;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;

#[RequestDto]
final class BasicTypesDTO
{
    #[BodyParam]
    public $stringParam;

    #[BodyParam]
    public $intParam;

    #[BodyParam]
    public $floatParam;

    #[BodyParam]
    public $boolParam;

    #[BodyParam]
    public $mixedParam;

    #[BodyParam]
    public $noTypeParam;
}
