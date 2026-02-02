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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Validator;

use Crtl\RequestDtoResolverBundle\Validator\GroupSequenceExtractor;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

final class GroupSequenceExtractorTest extends TestCase
{
    public function testGetGroupSequenceReturnsGroupsIfDefaultNotInGroups(): void
    {
        $extractor = new GroupSequenceExtractor();
        $object = new \stdClass();
        $groups = ['CustomGroup'];
        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn(\stdClass::class);

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertEquals($groups, $result);
    }

    public function testGetGroupSequenceReturnsGroupsIfClassNameNotInGroups(): void
    {
        $extractor = new GroupSequenceExtractor();
        $object = new \stdClass();
        $groups = [Constraint::DEFAULT_GROUP];
        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn('SomeOtherClass');

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertEquals($groups, $result);
    }

    public function testGetGroupSequenceReturnsStaticSequenceFromMetadata(): void
    {
        $extractor = new GroupSequenceExtractor();
        $object = new \stdClass();
        $className = \stdClass::class;
        $groups = [$className, Constraint::DEFAULT_GROUP];
        $sequence = new GroupSequence(['Group1', 'Group2']);

        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn($className);
        $metadata->method('hasGroupSequence')->willReturn(true);
        $metadata->method('getGroupSequence')->willReturn($sequence);

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertSame($sequence, $result);
    }

    public function testGetGroupSequenceReturnsSequenceFromGroupSequenceProviderInterface(): void
    {
        $extractor = new GroupSequenceExtractor();
        $object = $this->createMock(GroupSequenceProviderInterface::class);
        $className = get_class($object);
        $groups = [$className, Constraint::DEFAULT_GROUP];
        $sequence = ['Group1', 'Group2'];

        $object->method('getGroupSequence')->willReturn($sequence);

        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn($className);
        $metadata->method('hasGroupSequence')->willReturn(false);
        $metadata->method('isGroupSequenceProvider')->willReturn(true);

        // @phpstan-ignore function.alreadyNarrowedType
        if (method_exists($metadata, 'getGroupProvider')) {
            $metadata->method('getGroupProvider')->willReturn(null);
        }

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertInstanceOf(GroupSequence::class, $result);
        $this->assertEquals($sequence, $result->groups);
    }

    public function testGetGroupSequenceReturnsSequenceFromGroupProviderInterfaceViaLocator(): void
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (!method_exists(ClassMetadataInterface::class, 'getGroupProvider')) {
            self::markTestSkipped('External GroupProviderInterface not available');
        }

        $locator = $this->createMock(ContainerInterface::class);
        $extractor = new GroupSequenceExtractor($locator);

        $object = new \stdClass();
        $className = \stdClass::class;
        $groups = [$className, Constraint::DEFAULT_GROUP];
        $providerClass = 'App\Validator\MyGroupProvider';
        $sequence = ['GroupA', 'GroupB'];

        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn($className);
        $metadata->method('hasGroupSequence')->willReturn(false);
        $metadata->method('isGroupSequenceProvider')->willReturn(true);
        $metadata->method('getGroupProvider')->willReturn($providerClass);

        $provider = $this->createMock(GroupProviderInterface::class);
        $provider->method('getGroups')->with($object)->willReturn($sequence);

        $locator->method('get')->with($providerClass)->willReturn($provider);

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertInstanceOf(GroupSequence::class, $result);
        $this->assertEquals($sequence, $result->groups);
    }

    public function testGetGroupSequenceThrowsLogicExceptionWhenLocatorIsMissing(): void
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (!method_exists(ClassMetadataInterface::class, 'getGroupProvider')) {
            self::markTestSkipped('External GroupProviderInterface not available');
        }

        $extractor = new GroupSequenceExtractor(null);

        $object = new \stdClass();
        $className = \stdClass::class;
        $groups = [$className, Constraint::DEFAULT_GROUP];
        $providerClass = 'App\Validator\MyGroupProvider';

        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn($className);
        $metadata->method('hasGroupSequence')->willReturn(false);
        $metadata->method('isGroupSequenceProvider')->willReturn(true);
        // @phpstan-ignore function.alreadyNarrowedType
        if (method_exists($metadata, 'getGroupProvider')) {
            $metadata->method('getGroupProvider')->willReturn($providerClass);
        }

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('A group provider locator is required when using group provider.');

        $extractor->getGroupSequence($object, $groups, $metadata);
    }

    public function testGetGroupSequenceReturnsGroupsIfNoSequenceOrProviderDefined(): void
    {
        $extractor = new GroupSequenceExtractor();
        $object = new \stdClass();
        $className = \stdClass::class;
        $groups = [$className, Constraint::DEFAULT_GROUP];

        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getClassName')->willReturn($className);
        $metadata->method('hasGroupSequence')->willReturn(false);
        $metadata->method('isGroupSequenceProvider')->willReturn(false);

        $result = $extractor->getGroupSequence($object, $groups, $metadata);

        $this->assertEquals($groups, $result);
    }
}
