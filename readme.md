# crtl/request-dto-resolver-bundle

Symfony bundle to simplify instantiation and validation of request DTOs.


## Installation

```bash
composer crtl/request-dto-resolver-bundle
```

## Configuration

Register the bundle in your Symfony application. Add the following to your `config/bundles.php` file:

```php
return [
    // other bundles
    Crtl\RequestDTOResolverBundle\CrtlRequestDTOResolverBundle::class => ['all' => true],
];
```

## Usage

### Step 1: Create a DTO

Create a class to represent your request data. 
Annotate the class with `#[RequestDTO]` and use bellow attributes for properties to map request parameters.

```php
namespace App\DTO;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDTO]
class ExampleDTO
{
    #[BodyParam, Assert\NotBlank]
    public string $body;

    #[FileParam, Assert\NotNull]
    public $file;

    #[HeaderParam, Assert\NotBlank]
    public string $header;
    
    // Pass string to param if property does not match param name
    #[QueryParam('queryParamName'), Assert\NotBlank]
    public string $query;

    #[RouteParam, Assert\NotBlank]
    public string $route;
}
```

> By default each parameter is resolved by its property name.<br/> 
> If property name does not match parameter name you can pass an optional string to the constructor 
> of each `*Param` attribute (see [`AbstractParam::__construct`](src/Attribute/AbstractParam.php)).

### Step 2: Use the DTO in a Controller

Inject the DTO into your controller action. The [`RequestValueResolver`](src/RequestValueResolver.php) will automatically instantiate and validate the DTO.

```php
namespace App\Controller;

use App\DTO\ExampleDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
    #[Route('/example', name: 'example')]
    public function exampleAction(ExampleDTO $data): Response
    {
        // $data is an instance of ExampleDTO with validated request data
        return new Response('DTO received and validated successfully!');
    }
}
```

### Step 3: Handle Validation Errors

When validation fails, a [`Crtl\RequestDTOResolverBundle\Exception\RequestValidationException`](src/Exception/RequestValidationException.php) is thrown.
You can create an event listener or override the default exception handler to customize the response.

```php
namespace App\EventListener;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RequestValidationExceptionListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof RequestValidationException) {
            $response = new JsonResponse([
                'error' => 'Validation failed',
                'details' => $exception->getViolations(),
            ], JsonResponse::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }
}
```

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.