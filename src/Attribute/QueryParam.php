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

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDto} from {@link Request::$query}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class QueryParam extends AbstractNestedParam
{
    /**
     * One of "int", "float", "bool", "string" or a callable that returns one of these.
     * The transform type can be used to transform the type of a query param when receiving it.
     * Defaults to "string".
     *
     * @var (callable(string): mixed)|string
     */
    public readonly mixed $transformType;

    /**
     * @param (callable(string): mixed)|string $transformType
     */
    public function __construct(?string $name = null, callable|string $transformType = 'string')
    {
        parent::__construct($name);
        assert(is_callable($transformType) || in_array($transformType, ['int', 'float', 'bool', 'string'], true), "transformType must be callable or one of: 'int', 'float', 'bool', 'string'");

        $this->transformType = $transformType;
    }

    public function getValueFromRequest(Request $request): mixed
    {
        $value = parent::getValueFromRequest($request);

        if (null !== $value) {
            // TODO: rethink handling of arrays in query params
            return is_array($value) ? $value : $this->transformValue($value);
        }

        return null;
    }

    protected function getInputBag(Request $request): ParameterBag
    {
        return $request->query;
    }

    private function transformValue(mixed $value): mixed
    {
        if (is_callable($this->transformType)) {
            return call_user_func($this->transformType, $value);
        }

        return match ($this->transformType) {
            'int' => filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => null]]),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]),
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            default => (string) $value,
        };
    }
}
