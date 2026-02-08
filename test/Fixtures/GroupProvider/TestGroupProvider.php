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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\GroupProvider;

use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\DtoWithGroupSequenceProvider;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;

class TestGroupProvider implements GroupProviderInterface
{
    public function getGroups(object $object): array|GroupSequence
    {
        assert($object instanceof DtoWithGroupSequenceProvider);

        $groups = [self::class];

        if ('validate_second' === $object->first) {
            $groups[] = 'First';
        }

        return $groups;
    }
}
