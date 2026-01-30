# crtl/request-dto-resolver-bundle

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
    Crtl\RequestDTOResolverBundle\CrtlRequestDTOResolverBundle::class => ["all" => true],
];
```

## Usage

### Step 1: Create a DTO

Create a class to represent your request data. 
Annotate the class with [`#[RequestDto]`](src/Attribute/RequestDto.php) and use the attributes below for properties to map request parameters.

```php
namespace App\DTO;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
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
    #[BodyParam("nested"), Assert\Valid]
    public ?NestedRequestDTO $nestedBodyDto;
    
    // Optionally implement constructor which accepts request object
    // Only the request is passed; properties are NOT yet initialized here.
    public function __construct(Request $request) {
    
    }
}
```

> **IMPORTANT: Strict Typing**<br/>
> While strict types are supported, validation constraint mismatches can still lead to `TypeError` in production. Always ensure your constraints (e.g., `Assert\Type`, `Assert\NotBlank`) match your property types.

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

### Step 3: Handle Validation Errors

When validation fails, a [`Crtl\RequestDTOResolverBundle\Exception\RequestValidationException`](src/Exception/RequestValidationException.php) is thrown.

The bundle registers a default exception subscriber ([`RequestValidationExceptionEventSubscriber`](src/EventSubscriber/RequestValidationExceptionEventSubscriber.php)) with a low priority of **-1024**. This ensures that validation exceptions are caught and converted into a `JsonResponse` with a `400 Bad Request` status code by default.

You can still provide your own listener if you need custom error formatting:

```php
namespace App\EventListener;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestValidationExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Use a priority > -1024 to override the default bundle subscriber
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
