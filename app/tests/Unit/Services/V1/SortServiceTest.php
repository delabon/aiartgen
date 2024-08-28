<?php

namespace Tests\Unit\Services\V1;

use App\Services\V1\SortService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class SortServiceTest extends TestCase
{
    // Mock the Request class causes a deprecation notice by PHPUnit, So I'll have to create a fake request

    public function test_returns_desc_when_request_has_order_param_as_newest(): void
    {
        $request = Request::create('/', parameters: [
            'sort' => 'newest'
        ]);

        $queryService = new SortService($request);

        $this->assertSame('DESC', $queryService->getDirection());
    }

    public function test_returns_asc_when_request_has_order_param_as_oldest(): void
    {
        $request = Request::create('/', parameters: [
            'sort' => 'oldest'
        ]);

        $queryService = new SortService($request);

        $this->assertSame('ASC', $queryService->getDirection());
    }

    public function test_returns_desc_when_request_has_invalid_order(): void
    {
        $request = Request::create('/', parameters: [
            'sort' => 'jkdfajkhfd8'
        ]);

        $queryService = new SortService($request);

        $this->assertSame('DESC', $queryService->getDirection());
    }
}
