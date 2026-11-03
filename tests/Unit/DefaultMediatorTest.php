<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Contracts\DataRequest;
use App\Contracts\RequestHandler;
use App\Dto\AnimeSearchCommand;
use App\Support\DefaultMediator;
use Tests\TestCase;

final class DefaultMediatorTest extends TestCase
{
    public function testSendShouldReturnErrorResponseIfHandlerClassDoesntExist()
    {
        $mockHandler = \Mockery::mock(RequestHandler::class);
        $sut = new DefaultMediator([
            AnimeSearchCommand::class => fn () => $mockHandler,
        ]);

        $response = $sut->send(\Mockery::mock(DataRequest::class));
        $this->assertEquals(500, $response->status());
    }

    public function testSendShouldCallHandleOnHandlerIfFound()
    {
        $mockRequest = \Mockery::mock(DataRequest::class);
        $mockHandler = \Mockery::mock(RequestHandler::class);
        $mockHandler->expects()->handle($mockRequest)->once()->andReturns(response()->json([
            'message' => 'success',
        ]));

        $sut = new DefaultMediator([
            get_class($mockRequest) => fn () => $mockHandler,
        ]);

        $response = $sut->send($mockRequest);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(json_encode(['message' => 'success']), $response->content());
    }
}
