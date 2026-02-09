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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\Nested;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\AllTypesBodyParamsTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 3 layer deep nested dto.
 */
#[RequestDto]
class DeepNestedDto
{
    use AllTypesBodyParamsTrait;

    #[BodyParam]
    #[Assert\Valid]
    public ?DtoWithChildDto $child;

    /**
     * @var DtoWithChildDto[]
     */
    #[BodyParam]
    #[Assert\Valid]
    public array $children;
}
