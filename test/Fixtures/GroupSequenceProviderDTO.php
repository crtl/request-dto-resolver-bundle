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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Trait\RequestDtoTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[RequestDto]
#[Assert\GroupSequenceProvider]
final class GroupSequenceProviderDTO implements GroupSequenceProviderInterface
{
    use RequestDtoTrait;

    #[BodyParam]
    public ?string $first = null;

    #[BodyParam]
    #[Assert\NotBlank(groups: ['First'])]
    public ?string $second = null;

    public function getGroupSequence(): array
    {
        $groups = [self::class];

        if ('validate_second' === $this->getValue('first')) {
            $groups[] = 'First';
        }

        return $groups;
    }
}
