<?php

namespace App\Services\V1;

use Illuminate\Http\Request;

class SortService
{
    public function __construct(private Request $request)
    {
    }

    public function getDirection(): string
    {
        $sort = strtolower($this->request->query('order', 'newest'));
        $sort = in_array($sort, ['oldest', 'newest']) ? $sort : 'newest';

        return $sort === 'newest' ? 'DESC' : 'ASC';
    }
}
