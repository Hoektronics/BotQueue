<?php

namespace Tests\Feature\Host;


use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class HostErrorDuplicationTest extends TestCase
{
    /** @test */
    public function thereIsNoDuplicateCodesForHostErrors()
    {
        $reflection = new ReflectionClass(HostErrors::class);

        $publicStaticFilter = ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC;
        $staticMethods = collect($reflection->getMethods($publicStaticFilter));

        $errorResponses = $staticMethods
            ->map(function ($method) {
                /** @var ReflectionMethod $method */
                return $method->invoke(null);
            })
            ->filter(function ($result) {
                return $result instanceof ErrorResponse;
            })
            ->map(function ($result) {
                /** @var ErrorResponse $result */
                return $result->getCode();
            });

        $this->assertGreaterThan(0, $errorResponses->count());
        $this->assertEquals(
            $errorResponses->count(),
            $errorResponses->unique()->count(),
            "There is a duplicate error response code"
        );
    }
}