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

namespace Crtl\RequestDtoResolverBundle\Utility;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\UnionType;

/**
 * Internal helper used to process type info {@link Type}.
 *
 * @codeCoverageIgnore
 */
final class TypeHelper
{
    /**
     * @return array{builtInType: string, isNullable: bool, isCollection: bool, collectionValueType: ?Type}
     */
    public static function describe(?Type $type): array
    {
        if (null === $type) {
            return [
                'builtInType' => 'mixed',
                'isNullable' => true,
                'isCollection' => false,
                'collectionValueType' => null,
            ];
        }

        $isNullable = false;

        // Unwrap "?T"
        if (class_exists(NullableType::class) && $type instanceof NullableType) {
            $isNullable = true;
            $type = $type->getWrappedType();
        }

        // Unwrap "T|null" (or bigger unions) by picking the first non-null type
        if ($type instanceof UnionType) {
            /** @var Type $inner */
            foreach ($type->getTypes() as $inner) {
                if ($inner->isNullable()) {
                    $isNullable = true;
                    continue;
                }

                // Prefer non-null member
                $type = $inner;
                break;
            }
        }

        // Collections: builtin is basically "array"/"iterable"
        if ($type instanceof CollectionType) {
            return [
                'builtInType' => 'array',
                'isNullable' => $isNullable,
                'isCollection' => true,
                'collectionValueType' => $type->getCollectionValueType(),
            ];
        }

        if ($type instanceof Type\BuiltinType) {
            return [
                'builtInType' => $type->getTypeIdentifier()->name,
                'isNullable' => $isNullable,
                'isCollection' => false,
                'collectionValueType' => null,
            ];
        }

        if ($type instanceof Type\ObjectType) {
            return [
                'builtInType' => 'object',
                'isNullable' => $isNullable,
                'isCollection' => false,
                'collectionValueType' => null,
            ];
        }

        // Scalars / object / resource / callable, etc.
        // TypeInfo exposes this as the "builtin type" concept.
        return [
            'builtInType' => method_exists($type, 'getBuiltinType') ? $type->getBuiltinType() : null,
            'isNullable' => $isNullable,
            'isCollection' => false,
            'collectionValueType' => null,
        ];
    }
}
