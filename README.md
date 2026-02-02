# crtl/request-dto-resolver-bundle

!['codecov app svg image'](https://github.com/codecov/engineering-team/assets/152432831/e90313f4-9d3a-4b63-8b54-cfe14e7ec20d)
[![codecov](https://codecov.io/github/crtl/request-dto-resolver-bundle/graph/badge.svg?token=VLXDJ925T8)](https://codecov.io/github/crtl/request-dto-resolver-bundle)
[![Latest Stable Version](http://poser.pugx.org/crtl/request-dto-resolver-bundle/v)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![Total Downloads](http://poser.pugx.org/crtl/request-dto-resolver-bundle/downloads)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![Latest Unstable Version](http://poser.pugx.org/crtl/request-dto-resolver-bundle/v/unstable)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![License](http://poser.pugx.org/crtl/request-dto-resolver-bundle/license)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![PHP Version Require](http://poser.pugx.org/crtl/request-dto-resolver-bundle/require/php)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)


Symfony bundle for streamlined instantiation and validation of request DTOs.

## Features

1. **Automatic DTO Handling**: <br/>
    Instantly creates and validates Data Transfer Objects (DTOs) from `Request` data, that are type-hinted in controller actions.
2. **Symfony Validator Integration**:<br/>Leverages Symfony's built-in validator to ensure data integrity and compliance with your validation rules.
3. **Nested DTO Support**:<br/>Handles complex request structures by supporting nested DTOs for both query and body parameters, making it easier to manage hierarchical data.
4. **Strict Typing Support**:<br/>DTO properties can now be strictly typed, ensuring better code quality and IDE support.
5. **Flexible Query Transformation**:<br/>Built-in support for transforming query parameters into specific types (int, float, string, bool) or via custom callbacks.


## Installation

```bash
composer require crtl/request-dto-resolver-bundle
```

## Configuration

Register the bundle in your Symfony application. Add the following to your `config/bundles.php` file:

```php
return [
    // other bundles
    Crtl\RequestDtoResolverBundle\CrtlRequestDTOResolverBundle::class => ["all" => true],
];
```

## Usage

### Step 1: Create a DTO

Create a class to represent your request data. 
Annotate the class with [`#[RequestDto]`](src/Attribute/RequestDto.php) and use the attributes below for properties to map request parameters.

```php
namespace App\DTO;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class ExampleDTO
{
    // DTOs can now be strictly typed. 
    // Important: Validation constraints must be correct to prevent TypeErrors 
    // since hydration happens after validation.
    #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public string $someParam;

    // Matches file in uploaded files
    #[FileParam, Assert\NotNull]
    public mixed $file;
    
    // Matches Content-Type header in headers
    #[HeaderParam("Content-Type"), Assert\NotBlank]
    public string $contentType;
    
    // QueryParam supports optional transformType: "int", "float", "string", "bool" 
    // or a custom callback: fn(string $val) => ...
    #[QueryParam(name: "age", transformType: "int"), Assert\GreaterThan(18)]
    public int $age;

    // Matches id 
    #[RouteParam, Assert\NotBlank]
    public string $id;
    
    // Nested DTOs are supported for BodyParam and QueryParam
    #[BodyParam("nested")] // Dont use Assert\Valid on nested DTOs otherwise native validation is triggered
    public ?NestedRequestDTO $nestedBodyDto;
    
    // Optionally implement constructor which accepts request object
    // Only the request is passed; properties are NOT yet initialized here.
    public function __construct(Request $request) {
    
    }
}
```

> **IMPORTANT: Strict Typing**<br/>
> While strict types are supported, validation constraint mismatches can still lead to `TypeError` in production. Always ensure your constraints (e.g., `Assert\Type`, `Assert\NotBlank`) match your property types.

> > **IMPORTANT: Nested DTOs**<br/>
> Dont use any assertions on nested DTO properties as this will trigger the native validation fow eventually breaking hydration and triggering errors.

### DTO Lifecycle

1. **Resolution**: `RequestDtoResolver` instantiates the DTO during the controller argument resolving phase. Only the `Request` object is passed to the constructor.
2. **Security**: Symfony security checks (e.g., `#[IsGranted]`) are executed.
3. **Validation & Hydration**: An event subscriber (`RequestDtoValidationEventSubscriber`) listens to `kernel.controller_arguments`. It validates the DTO data and, if successful, hydrates the DTO properties.

### Step 2: Use the DTO in a Controller

Inject the DTO into your controller action. It will be automatically instantiated, validated, and hydrated.

```php
namespace App\Controller;

use App\DTO\ExampleDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
    #[Route("/example", name: "example")]
    public function exampleAction(ExampleDTO $data): Response
    {
        // $data is an instance of ExampleDTO with validated and hydrated request data
        return new Response("DTO received and validated successfully!");
    }
}
```

### Using RequestDtoTrait

The [`RequestDtoTrait`](src/Trait/RequestDtoTrait.php) provides a default constructor that accepts the `Request` object and a `getValue(string $property)` method. This method is useful for accessing request data before hydration, which can come in handy in group sequence providers.

```php
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Trait\RequestDtoTrait;
use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;

#[RequestDto]
class MyDTO
{
    use RequestDtoTrait;

    #[BodyParam]
    public string $type;
}
```

### Validation Group Sequences

When using [Group Sequences](https://symfony.com/doc/current/validation/sequence_provider.html) to define conditional validation, you must be careful about how you access data.

**IMPORTANT: Uninitialized Properties**

Since hydration happens *after* validation, DTO properties are **uninitialized** when the group sequence is evaluated. Accessing them directly will throw an `Error`.

To safely access request parameters in your group sequence logic, use `RequestDtoTrait::getValue()`:

```php
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Trait\RequestDtoTrait;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[RequestDto]
class MyDTO implements GroupSequenceProviderInterface
{
    use RequestDtoTrait;

    #[BodyParam]
    public string $type;

    public function getGroupSequence(): array|GroupSequence
    {
        // Use getValue() instead of $this->type
        $type = $this->getValue("type");

        $groups = ["MyDTO"];
        if ($type === "special") {
            $groups[] = "Special";
        }

        return $groups;
    }
}
```

#### Using a Group Sequence Provider Service

You can also use a service to provide the group sequence. This is useful if your validation logic depends on external services (e.g., a database or configuration).

1. **Create the Provider Service**:

```php
namespace App\Validator;

use App\DTO\MyDTO;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;

class MyGroupSequenceProvider implements GroupProviderInterface
{
    public function getGroups(object $object): array|GroupSequence
    {
        assert($object instanceof MyDTO)
    
        $groups = ["MyDTO"];

        // Use getValue() to safely access uninitialized properties
        if ($object->getValue("type") === "special") {
            $groups[] = "Special";
        }

        return $groups;
    }
}
```

2. **Configure the DTO**:

```php
use App\Validator\MyGroupSequenceProvider;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Trait\RequestDtoTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
#[Assert\GroupSequenceProvider(provider: MyGroupSequenceProvider::class)]
class MyDTO
{
    // Trait is important to access fields before validation
    use RequestDtoTrait;
    
    // ...
}
```

> **Note**: `getValue()` only works in **root DTOs**. It uses reflection to resolve data from the request, which cannot access parent data in nested contexts.

### Step 3: Handle Validation Errors

When validation fails, a [`Crtl\RequestDtoResolverBundle\Exception\RequestValidationException`](src/Exception/RequestValidationException.php) is thrown.

The bundle registers a default exception subscriber ([`RequestValidationExceptionEventSubscriber`](src/EventSubscriber/RequestValidationExceptionEventSubscriber.php)) with a low priority of **-32**. This ensures that validation exceptions are caught and converted into a `JsonResponse` with a `400 Bad Request` status code by default.

You can still provide your own listener if you need custom error formatting:

```php
namespace App\EventListener;

use Crtl\RequestDtoResolverBundle\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestValidationExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Use a priority > -32 to override the default bundle subscriber
            KernelEvents::EXCEPTION => ["onKernelException", 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof RequestValidationException) {
            $response = new JsonResponse([
                "error" => "Validation failed",
                "details" => $exception->getViolations(),
            ], JsonResponse::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }
}
```

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
