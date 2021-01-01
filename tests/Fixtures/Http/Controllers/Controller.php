<?php

namespace Tests\Fixtures\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Tests\Fixtures\Traits\ForwardsCallsToPrivateMethods;

class Controller extends BaseController
{
    use ForwardsCallsToPrivateMethods;
}
