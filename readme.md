# crtl/request-dto-resolver-bundle

![Coverage Badge](coverage.svg)

Symfony bundle for streamlined instantiation and validation of request DTOs.

## Features

1. **Automatic DTO Handling**: <br/>
    Instantly creates and validates Data Transfer Objects (DTOs) from `Request` data, that are type-hinted in controller actions.
2. **Symfony Validator Integration**:<br/>Leverages Symfony's built-in validator to ensure data integrity and compliance with your validation rules.
3. **Nested DTO Support**:<br/>Handles complex request structures by supporting nested DTOs for both query and body parameters, making it easier to manage hierarchical data.


## Installation

```bash
composer crtl/request-dto-resolver-bundle
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
Annotate the class with [`#[RequestDTO]`](src/Attribute/RequestDTO.php) and use bellow attributes for properties to map request parameters.

```php
namespace App\DTO;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDTO]
class ExampleDTO
{
    // Matches someParam in request body
    #[BodyParam, Assert\NotBlank]
    public ?string $someParam;

    // Matches file in uploaded files
    #[FileParam, Assert\NotNull]
    public mixed $file;
    
    // Matches Content-Type header in headers
    #[HeaderParam("Content-Type"), Assert\NotBlank]
    public string $contentType;
    
    // Pass string to param if property does not match param name.
    // Matches queryParamName in query params
    #[QueryParam("queryParamName"), Assert\NotBlank]
    public string $query;

    // Matches id 
    #[RouteParam, Assert\NotBlank]
    public string $id;
    
    // Nested DTOs are supported for BodyParam and QueryParam
    #[BodyParam("nested"), Assert\Valid]
    public ?NestedRequestDTO $nestedBodyDto;
    
    #[QueryParam("nested")]
    public ?NestedRequestDTO $nestedQueryParamDto;
    
    // Optionally implement constructor which accepts request object
    public function __construct(Request $request) {
    
    }
}
```

> **IMPORTANT**<br/>
> Each property must accept the type `?string` except properties that are request DTOs.<br/>
> Otherwise PHP may throw TypeErrors at runtime because we cannot know that the request contains the valid data type before validation.


> By default, each parameter is resolved by its property name.<br/> 
> If property name does not match parameter name you can pass an optional string to the constructor 
> of each `*Param` attribute (see [`AbstractParam::__construct`](src/Attribute/AbstractParam.php)).

> Each DTO can define an optional constructor which accepts a [`Request`](vendor/symfony/http-foundation/Request.php) object 

### Step 2: Use the DTO in a Controller

Inject the DTO into your controller action. The [`RequestValueResolver`](src/RequestDTOResolver.php) will automatically instantiate and validate the DTO.

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
        // $data is an instance of ExampleDTO with validated request data
        return new Response("DTO received and validated successfully!");
    }
}
```

### Step 3: Handle Validation Errors

> When validation fails, a [`Crtl\RequestDTOResolverBundle\Exception\RequestValidationException`](src/Exception/RequestValidationException.php) is thrown.
> 
You can create an event listener or override the default exception handler and handle validation errors.

```php
namespace App\EventListener;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RequestValidationExceptionListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => "onKernelException",
        ];
    }

    public function onKernelException(ExceptionEvent $event)
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