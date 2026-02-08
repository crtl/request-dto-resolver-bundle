
# crtl/request-dto-resolver-bundle

[![codecov](https://codecov.io/gh/crtl/request-dto-resolver-bundle/branch/2.x/graph/badge.svg?token=VLXDJ925T8)](https://codecov.io/gh/crtl/request-dto-resolver-bundle)
[![Latest Stable Version](http://poser.pugx.org/crtl/request-dto-resolver-bundle/v)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![Total Downloads](http://poser.pugx.org/crtl/request-dto-resolver-bundle/downloads)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![Latest Unstable Version](http://poser.pugx.org/crtl/request-dto-resolver-bundle/v/unstable)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![License](http://poser.pugx.org/crtl/request-dto-resolver-bundle/license)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)
[![PHP Version Require](http://poser.pugx.org/crtl/request-dto-resolver-bundle/require/php)](https://packagist.org/packages/crtl/request-dto-resolver-bundle)

A Symfony bundle for predictable, type-safe instantiation and validation of request DTOs.

It removes boilerplate from controllers while staying close to Symfony’s
native validation and argument resolving mechanisms.

## Features

- **Automatic DTO Resolution**  
  DTOs type-hinted in controller actions are instantiated and validated automatically.

- **Native Symfony Validator Integration**  
  Uses Symfony’s `ValidatorInterface` without custom validation layers.

- **Nested DTO Support**  
  Supports complex request payloads with nested DTOs for query, body, header, file and route parameters.

- **Strict Typing Friendly**  
  DTO properties can be strictly typed for better IDE support and safer refactoring.

- **Flexible Query Parameter Transformation**  
  Query parameters can be transformed to scalar types or via custom callbacks.

## Installation

```bash
composer require crtl/request-dto-resolver-bundle
````

## Configuration

Register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    Crtl\RequestDtoResolverBundle\CrtlRequestDtoResolverBundle::class => ["all" => true],
];
```

## Usage

### Step 1: Define a Request DTO

Create a DTO class and annotate it with `#[RequestDto]`.
Use parameter attributes to map request data to properties.

> **The attribute is required to identify which controller arguments should be resolved and validated.**


### 1.1 Strictly typed DTO

```php
namespace App\DTO;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class ExampleDTO
{
    #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public string $someParam;

    #[FileParam, Assert\NotNull]
    public ?UploadedFile $file;

    #[HeaderParam("Content-Type"), Assert\NotBlank]
    public string $contentType;

    #[QueryParam(name: "age", transformType: "int"), Assert\GreaterThan(18)]
    public int $age;

    #[RouteParam, Assert\NotBlank]
    public string $id;

    // Nested DTOs are supported for BodyParam and QueryParam
    // Do NOT use Assert\Valid here
    #[BodyParam("nested")]
    public ?NestedRequestDTO $nestedBodyDto;

    // Optional constructor receiving the Request
    // Properties are not initialized at this stage
    public function __construct(Request $request)
    {
    }
}
```

> **Any type mismatches will trigger a constraint violation and thus a `RequestValidationException` is thrown.**

### 1.2 Mixed typed DTO
```php
namespace App\DTO;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class ExampleDTO
{
    #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public string $someParam;
    
    /**
     * @var string 
     */
     #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public mixed $withDefaultValue = "My default value";

    #[FileParam, Assert\NotNull]
    public ?UploadedFile $file;

    #[HeaderParam("Content-Type"), Assert\NotBlank]
    public string $contentType;

    // Because query params are all strings by default
    // we can provide a type transformer to transform its type.
    // values are converted using filter_var with the corrosponding FILTER_VALIDATE_* option.
    #[QueryParam(name: "age", transformType: "int"), Assert\GreaterThan(18)]
    public int $age;
    
    // Or provide a custom callable to tranform type
    #[
        QueryParam(
            name: "age", 
            transformType: fn(string $value) => strtolower($value)
        ), 
        Assert\GreaterThan(18)
    ]
    public mixed $customQueryParam;

    #[RouteParam, Assert\NotBlank]
    public string $id;

    // Nested DTOs are supported for BodyParam and QueryParam
    // Do NOT use Assert\Valid here
    #[BodyParam("nested")]
    public ?NestedRequestDTO $nestedBodyDto;

    // Optional constructor receiving the Request
    // It is recommended to make the request argument nullable to support creation of DTOs from
    // array data but not required when only used in HTTP contexts.
    public function __construct(?Request $request = null)
    {
    }
}
```

### Step 2: Use the DTO in a Controller

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
        return new Response("DTO received and validated successfully!");
    }
}
```

### DTO Lifecycle

1. **Resolution**
   `RequestDtoResolver` instantiates the DTO during controller argument resolving.

2. **Security**
   Symfony security checks (e.g. `#[IsGranted]`) are executed.

3. **Validation**
   An event subscriber validates the DTO and hydrates its properties if validation passes, otherwise an `Crtl\RequestDtoResolverBundle\Exception\RequestValidationException` is thrown.

### Validation Group Sequences

Though all variations of group sequence providers are supported you still have
to consider unitialized properties when using strict types because of invalid input.
Make sure to ensure properties are initialized using `isset()` or reflection.

### Handling Validation Errors

On validation failure, a `RequestValidationException` is thrown.

> **The bundle registers a default exception subscriber (priority **-32**) that
returns a `400 Bad Request` JSON response.**

You can override this with your own listener:

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
            KernelEvents::EXCEPTION => ["onKernelException", 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof RequestValidationException) {
            $event->setResponse(new JsonResponse([
                "error" => "Validation failed",
                "details" => $exception->getViolations(),
            ], JsonResponse::HTTP_BAD_REQUEST));
        }
    }
}
```

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.