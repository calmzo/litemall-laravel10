<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function assertLitemallApiGet($url, $ignore = [])
    {
        $this->assertLitemallApi($url, 'get', [], $ignore);
    }

    public function assertLitemallApiPost($url, $data = [], $ignore = [])
    {
        $this->assertLitemallApi($url, 'post', $data, $ignore);
    }

    public function assertLitemallApi($url, $method = 'get', $data = [], $ignore = [])
    {
        $client = new Client();
        if ($method == 'get') {
            $response1 = $this->get($url, $this->getAuthHeader());
            $response2 = $client->get('http://127.0.0.1:8080' . $url,
                ['headers' => ['X-Litemall-Token' => $this->token]]);
        } else {
            $response1 = $this->post($url, $data, $this->getAuthHeader());
            $response2 = $client->post('http://127.0.0.1/' . $url, [
                ['headers' => ['X-Litemall-Token' => $this->token]],
                'json' => $data
            ]);
        }
        $content1 = $response1->getContent();
        $content1 = json_encode(json_decode($content1, true), JSON_UNESCAPED_UNICODE);
        echo "mcshop => $content1" . PHP_EOL;
        $content1 = json_decode($content1, true);
        $content2 = $response2->getBody()->getContents();
        echo "litemall => $content2" . PHP_EOL;
        $content2 = json_decode($content2, true);
        foreach ($ignore as $key) {
            unset($content1[$key]);
            unset($content2[$key]);
        }
        $this->assertEquals($content2, $content1);
    }

    public function getAuthHeader($username = 'yfy12', $password = '123')
    {
        $response = $this->post('/wx/auth/login', ['username' => $username, 'password' => $password]);
        $content  = $response->getOriginalContent();
        Log::debug('content', $content);
        $token       = $content['data']['token'];
        $this->token = $token;
        return ['Authorization' => "Bearer {$token}"];
    }
}
