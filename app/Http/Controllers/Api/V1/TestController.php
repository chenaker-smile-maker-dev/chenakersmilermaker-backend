<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Test', weight: 1)]
class TestController extends BaseController
{
    /**
     * Test api.
     *
     * returns the user data if connected.
     */
    public function index(Request $request)
    {
        return $this->sendResponse([
            'user' => $request->user()
        ]);
    }
}
