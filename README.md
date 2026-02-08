
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


#### 1.1 Strictly typed DTO

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
class ExampleDto
{
    #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public string $someParam;
    
     #[BodyParam, Assert\NotBlank, Assert\Type("string")]
    public string $withDefaultValue = "My default value";
    
    #[BodyParam]
    public ?string $nullable;

    #[FileParam, Assert\NotNull]
    public ?UploadedFile $file;

    #[HeaderParam("Content-Type"), Assert\NotBlank]
    public string $contentType;

    // Because query params are all strings by default
    // we can provide a type transformer to transform its type.
    // values are converted using filter_var with the corrosponding FILTER_VALIDATE_* option.
    #[QueryParam(transformType: "int"), Assert\GreaterThan(18)]
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

> **Any type mismatches will trigger a constraint violation and thus a `RequestValidationException` is thrown.**

#### 1.2 Mixed typed DTO
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
class ExampleDto
{
    /**
     * @var string
     */
    #[BodyParam, Assert\NotBlank]
    public mixed $contentType;
}
```

### 1.3 Important notes about DTO hydration

- **Uninitialized properties**  
  If a request does not contain data for a property, that property will remain uninitialized.  
  To guarantee initialization, either:
    - provide a default value, or
    - add appropriate validation constraints (e.g. `NotNull`, `NotBlank`).

- **Hydration from arrays**  
  When a DTO is manually hydrated from an array using `RequestDtoFactory::fromArray()`, the configured `AbstractParam::$name` is ignored.  
  In this case, the DTO’s property names are always used as the source keys.

- **Validation still runs in strict mode**  
  Even if some properties cannot be assigned due to type mismatches in strict typing mode, the DTO is still fully validated using Symfony’s validator.  
  Any resulting violations will lead to a `RequestValidationException`.

### 1.4 Validation group sequences

All Symfony validation group sequence variants are supported.

Because request data can never be trusted, **DTO properties may be uninitialized regardless of whether strict typing is used or not**.  
Missing or invalid input can prevent a property from being assigned during hydration.

When using validation group sequences, you must therefore ensure that properties are accessed safely by:
- checking initialization with `isset()`, or
- using reflection-based checks when necessary.

Failing to do so may lead to runtime errors before later validation groups are evaluated.


### Step 2: Use the DTO in a Controller

```php
namespace App\Controller;

use App\DTO\ExampleDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
    #[Route("/example", name: "example")]
    public function exampleAction(ExampleDto $data): Response
    {
        return new Response("DTO received and validated successfully!");
    }
}
```

---

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