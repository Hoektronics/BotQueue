<?php

namespace Tests\Feature\Host;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;

abstract class HostTestCase extends TestCase
{
    use HasUser;
    use HasHost;
    use PassportHelper;
    use RefreshDatabase;
}
