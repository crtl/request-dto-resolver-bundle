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

namespace Crtl\RequestDtoResolverBundle\PropertyInfo;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class PropertyInfoExtractorFactory
{
    private PropertyInfoExtractor $extractor;

    public function __construct(
        private readonly PhpDocExtractor $phpDocExtractor,
        private readonly ReflectionExtractor $reflectionExtractor,
    ) {
    }

    public function create(): PropertyInfoExtractor
    {
        if (!isset($this->extractor)) {
            // list of PropertyListExtractorInterface (any iterable)
            $listExtractors = [$this->reflectionExtractor];

            // list of PropertyTypeExtractorInterface (any iterable)
            $typeExtractors = [$this->phpDocExtractor, $this->reflectionExtractor];

            // list of PropertyDescriptionExtractorInterface (any iterable)
            $descriptionExtractors = [$this->phpDocExtractor];

            // list of PropertyAccessExtractorInterface (any iterable)
            $accessExtractors = [$this->reflectionExtractor];

            // list of PropertyInitializableExtractorInterface (any iterable)
            $propertyInitializableExtractors = [$this->reflectionExtractor];

            $this->extractor = new PropertyInfoExtractor(
                $listExtractors,
                $typeExtractors,
                $descriptionExtractors,
                $accessExtractors,
                $propertyInitializableExtractors,
            );
        }

        return $this->extractor;
    }
}
