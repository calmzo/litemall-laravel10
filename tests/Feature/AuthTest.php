<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testRegister()
    {
        $response = $this->post('wx/auth/register', ['username' => 'test2', 'password' => '123456', 'mobile' => '13111111112', 'code' => '496459']);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals('0', $ret['errno']);
        $this->assertNotEmpty($ret['data']);

    }
}
