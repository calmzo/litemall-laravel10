<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GrouponTest extends TestCase
{
    public function testList(): void
    {
        $this->assertLitemallApiGet('wx/groupon/list');
        $this->assertLitemallApiGet('wx/groupon/list?page=2&limit=5');
    }
}
