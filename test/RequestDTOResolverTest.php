<?php

namespace Crtl\RequestDTOResolverBundle\Test;

use Crtl\RequestDTOResolverBundle\Attribute;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Crtl\RequestDTOResolverBundle\RequestDTOResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Attribute\RequestDTO]
class TestDTO
{
    #[Attribute\BodyParam, Assert\NotBlank]
    public ?string $param;

    #[Attribute\FileParam("fileParam"), Assert\NotNull]
    public ?UploadedFile $file;

    #[Attribute\HeaderParam("headerParam"), Assert\NotBlank]
    public ?string $header;

    #[Attribute\QueryParam("queryParam"), Assert\NotBlank]
    public ?string $query;

    #[Attribute\RouteParam("routeParam"), Assert\NotBlank]
    public ?string $route;
}

#[Attribute\RequestDTO]
class PrivateConstructorClass
{
    private function __construct()
    {
    }
}

class RequestDTOResolverTest extends TestCase
{

    protected ValidatorInterface $validator;

    protected LoggerInterface $logger;

    protected RequestDTOResolver $resolver;


    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resolver = new RequestDTOResolver($this->validator);
        $this->resolver->setLogger($this->logger);
    }

    public function testResolveReturnsEmptyArrayForInvalidCandidates()
    {
        $request = new Request();

        // List of invalid types
        /** @var ArgumentMetadata[] $tests */
        $tests = [
            new ArgumentMetadata("test", null, false, false, null),
            new ArgumentMetadata("test", "int", false, false, null),
            new ArgumentMetadata("test", "float", false, false, null),
            new ArgumentMetadata("test", "string", false, false, null),
            new ArgumentMetadata("test", "bool", false, false, null),
            new ArgumentMetadata("test", "array", false, false, null),
            new ArgumentMetadata("test", "callable", false, false, null),
            new ArgumentMetadata("test", "iterable", false, false, null),
            new ArgumentMetadata("test", "object", false, false, null),
            new ArgumentMetadata("test", "mixed", false, false, null),

            // Test nonexistent class
            new ArgumentMetadata("test", "SomeRandom\\Namespace\\IOJGIOASJGOL\\NonExistentClass", false, false, null),

            // Test anonymous class which does not have Request attribute
            new ArgumentMetadata("test", get_class(new class {
            }), false, false, null),
        ];

        foreach ($tests as $test) {
            $result = $this->resolver->resolve($request, $test);

            $this->assertEquals(
                [],
                $result,
                sprintf("%s::resolve did not return empty array for type %s", RequestDTOResolver::class, $test->getType())
            );
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testResolveReturnsNewInstance()
    {
        $request = new Request(
            ["queryParam" => "value"],
            ["param" => "value"],
            ["_route_params" => ["routeParam" => "value"]],
            [],
            ["fileParam" => $this->createMock(UploadedFile::class)],
            ["HTTP_headerParam" => "value"]
        );

        $argument = new ArgumentMetadata("test", TestDTO::class, false, false, null);

        $this->validator->method("validate")->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf($argument->getType(), $result[0]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testResolvePassesRequestToConstructor()
    {
        $request = new Request();


        $argument = new ArgumentMetadata("test", get_class(new #[Attribute\RequestDTO] class {
            public function __construct(public ?Request $request = null)
            {
            }
        }), false, false, null);

        $this->validator->method("validate")->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf($argument->getType(), $result[0]);
        $this->assertSame($request, $result[0]->request);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testResolveThrowsException()
    {
        $this->expectException(RequestValidationException::class);

        $request = new Request(
            ["queryParam" => "value"],
            ["param" => "value"],
            ["_route_params" => ["routeParam" => "value"]],
            [],
            ["fileParam" => $this->createMock(UploadedFile::class)],
            ["HTTP_headerParam" => "value"]
        );

        $argument = new ArgumentMetadata("test", TestDTO::class, false, false, null);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method("count")->willReturn(1);

        $this->validator->method("validate")->willReturn($violations);

        $this->resolver->resolve($request, $argument);
    }

    public function testReturnsEmptyResultIfConstructorIsPrivate()
    {
        $argument = new ArgumentMetadata("test", PrivateConstructorClass::class, false, false, null);

        $result = $this->resolver->resolve(new Request(), $argument);
        $this->assertCount(0, $result);
    }


}
