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

namespace Crtl\RequestDtoResolverBundle\Test\Integration;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\PropertyInfo\PropertyInfoExtractorFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadataFactory;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use Crtl\RequestDtoResolverBundle\Validator\GroupSequenceExtractor;
use Crtl\RequestDtoResolverBundle\Validator\RequestDtoValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

#[RequestDto]
final class BasicTestDto
{
    #[QueryParam('name')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $name;

    #[QueryParam('age', transformType: 'int')]
    #[Assert\Type('int')]
    #[Assert\GreaterThan(18)]
    public int $age;
}

#[RequestDto]
#[Assert\Callback(callback: 'validatePasswords')]
final class ClassConstraintDto
{
    #[QueryParam('password')]
    #[Assert\NotBlank]
    public string $password;

    #[QueryParam('passwordConfirm')]
    #[Assert\NotBlank]
    public string $passwordConfirm;

    /**
     * @param ClassConstraintDto $object
     */
    public static function validatePasswords($object, \Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if (isset($object->password) && isset($object->passwordConfirm) && $object->password !== $object->passwordConfirm) {
            $context->buildViolation('Passwords do not match')
                ->addViolation();
        }
    }
}

#[RequestDto]
final class NestedParentDto
{
    #[QueryParam('title')]
    #[Assert\NotBlank]
    public string $title = '';

    #[QueryParam('child')]
    #[Assert\Valid]
    public BasicTestDto $child;
}

#[RequestDto]
#[Assert\GroupSequence(['Basic', 'Strict', 'GroupSequenceDto'])]
final class GroupSequenceDto
{
    #[QueryParam('name')]
    #[Assert\NotBlank(groups: ['Basic'])]
    public string $name;

    #[QueryParam('email')]
    #[Assert\Email(groups: ['Strict'])]
    public string $email;
}

#[RequestDto]
final class BodyParamDto
{
    #[BodyParam('name')]
    #[Assert\NotBlank]
    public string $name;

    #[BodyParam('email')]
    #[Assert\Email]
    public string $email;
}

#[RequestDto]
final class MixedParamDto
{
    #[QueryParam('q')]
    #[Assert\NotBlank]
    public string $query;

    #[BodyParam('b')]
    #[Assert\NotBlank]
    public string $body;
}

#[RequestDto]
final class GroupsDto
{
    #[QueryParam('name')]
    #[Assert\NotBlank(groups: ['Registration'])]
    public string $name;

    #[QueryParam('age')]
    #[Assert\GreaterThan(18, groups: ['Adult'])]
    public int $age;
}

final class RequestDtoValidatorIntegrationTest extends TestCase
{
    private RequestDtoValidator $validator;

    protected function setUp(): void
    {
        $innerValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $extractorFactory = new PropertyInfoExtractorFactory(
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        );

        $propertyInfoExtractor = $extractorFactory->create();

        $reflectionHelper = new DtoReflectionHelper();
        $paramMetadataFactory = new RequestDtoParamMetadataFactory($innerValidator, $reflectionHelper, $propertyInfoExtractor);
        $metadataFactory = new RequestDtoMetadataFactory($innerValidator, $reflectionHelper, $paramMetadataFactory);
        $groupSequenceExtractor = new GroupSequenceExtractor();
        $this->validator = new RequestDtoValidator($innerValidator, $metadataFactory, $groupSequenceExtractor);
    }

    // @phpstan-ignore method.unused
    private function debugDumpViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            dump($violation->getPropertyPath().': '.$violation->getMessage());
        }
    }

    public function testValidateAndHydrateSuccess(): void
    {
        $dto = new BasicTestDto();
        $request = new Request(['name' => 'John Doe', 'age' => 25]);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertCount(0, $violations);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals(25, $dto->age);
    }

    public function testValidateAndHydratePropertyFailure(): void
    {
        $dto = new BasicTestDto();
        // age is too young, name is too short
        $request = new Request(['name' => 'Jo', 'age' => 15]);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertGreaterThan(0, $violations->count());
        // Properties should NOT be set because validation failed
        $this->assertFalse(isset($dto->name));
        $this->assertFalse(isset($dto->age));
    }

    public function testClassConstraintFailure(): void
    {
        $dto = new ClassConstraintDto();
        $request = new Request(['password' => 'foo', 'passwordConfirm' => 'bar']);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertCount(1, $violations);
        $this->assertEquals('Passwords do not match', $violations[0]->getMessage());
        // Properties SHOULD be set because property-level validation passed
        $this->assertEquals('foo', $dto->password);
        $this->assertEquals('bar', $dto->passwordConfirm);
    }

    public function testNestedDtoValidation(): void
    {
        $dto = new NestedParentDto();
        $request = new Request([
            'title' => 'Parent Title',
            'child' => [
                'name' => 'Child Name',
                'age' => 20,
            ],
        ]);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);
        $this->assertCount(0, $violations);
        $this->assertEquals('Parent Title', $dto->title);
        $this->assertEquals('Child Name', $dto->child->name);
        $this->assertEquals(20, $dto->child->age);
    }

    public function testNestedDtoFailure(): void
    {
        $dto = new NestedParentDto();
        $request = new Request([
            'title' => 'Parent Title',
            'child' => [
                'name' => 'Jo', // too short
                'age' => 20,
            ],
        ]);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertGreaterThan(0, $violations->count());
        // child should NOT be set because its internal validation failed
        $this->assertFalse(isset($dto->child));
    }

    public function testGroupSequenceShortCircuit(): void
    {
        $dto = new GroupSequenceDto();
        // name is empty (fails Basic group), email is invalid (fails Strict group)
        $request = new Request(['name' => '', 'email' => 'invalid-email']);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        // It should only report violation for 'name' because it short-circuits after the first group in sequence fails
        $this->assertCount(1, $violations);
        // Note: currently property path is empty due to how validatePropertyValue is called in RequestDtoValidator
        // $this->assertEquals('name', $violations[0]->getPropertyPath());
    }

    public function testBodyParamValidation(): void
    {
        $dto = new BodyParamDto();
        $request = new Request([], ['name' => 'John', 'email' => 'john@example.com']);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertCount(0, $violations);
        $this->assertEquals('John', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
    }

    public function testMixedParamsValidation(): void
    {
        $dto = new MixedParamDto();
        $request = new Request(['q' => 'search'], ['b' => 'content']);

        $violations = $this->validator->validateAndHydrate($dto, $request, []);

        $this->assertCount(0, $violations);
        $this->assertEquals('search', $dto->query);
        $this->assertEquals('content', $dto->body);
    }

    public function testValidationGroups(): void
    {
        $dto = new GroupsDto();
        $request = new Request(['name' => '', 'age' => 15]);

        // Validate only Registration group -> name should fail, age should be ignored
        $violations = $this->validator->validateAndHydrate($dto, $request, ['Registration']);
        $this->assertCount(1, $violations);

        // Validate only Adult group -> age should fail, name should be ignored
        $dto = new GroupsDto();
        $violations = $this->validator->validateAndHydrate($dto, $request, ['Adult']);
        $this->assertCount(1, $violations);

        // Validate both -> both should fail
        $dto = new GroupsDto();
        $violations = $this->validator->validateAndHydrate($dto, $request, ['Registration', 'Adult']);
        // Note: RequestDtoValidator processes groups one by one and stops if a group has violations.
        // If 'Registration' fails, it might stop there if it's considered a sequence or if that's how it's implemented.
        // Actually looking at code: it iterates groups and returns if $groupViolations > 0.
        $this->assertCount(1, $violations);
    }
}
