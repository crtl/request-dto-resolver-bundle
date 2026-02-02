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

namespace Crtl\RequestDtoResolverBundle\Validator;

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

class GroupSequenceExtractor
{
    public function __construct(
        private readonly ?ContainerInterface $groupProviderLocator = null,
    ) {
    }

    /**
     * @param array<string[]|string|GroupSequence> $groups
     *
     * @return string[]|mixed[]|GroupSequence
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getGroupSequence(object $object, array $groups, ClassMetadataInterface $metadata): array|GroupSequence
    {
        $className = $metadata->getClassName();

        // Return groups when not in default group
        if (!in_array($className, $groups) || !in_array(Constraint::DEFAULT_GROUP, $groups)) {
            return $groups;
        }

        if ($metadata->hasGroupSequence()) {
            // The group sequence is statically defined for the class
            return $metadata->getGroupSequence();
        } elseif ($metadata->isGroupSequenceProvider()) {
            // @phpstan-ignore function.alreadyNarrowedType
            if (method_exists($metadata, 'getGroupProvider') && null !== $provider = $metadata->getGroupProvider()) {
                if (null === $this->groupProviderLocator) {
                    throw new \LogicException('A group provider locator is required when using group provider.');
                }

                /** @var GroupProviderInterface $provider */
                $provider = $this->groupProviderLocator->get($provider);
                $group = $provider->getGroups($object);
            } else {
                assert($object instanceof GroupSequenceProviderInterface);
                // The group sequence is dynamically obtained from the validated
                // object
                $group = $object->getGroupSequence();
            }

            if (!$group instanceof GroupSequence) {
                $group = new GroupSequence($group);
            }

            return $group;
        }

        return $groups;
    }
}
