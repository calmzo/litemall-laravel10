<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;
use App\Input\OrderGoodsSubmit;
use App\Models\Goods\GoodsProduct;
use App\Models\Promotion\GrouponRules;
use App\Models\User\User;
use App\Services\Order\CartServices;
use App\Services\Order\OrderServices;
use App\Services\User\AddressServices;
use GuzzleHttp\Client;
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
//        $client = new Client();
        if ($method == 'get') {
            $response1 = $this->get($url, $this->getAuthHeader());
//            $response2 = $client->get('http://127.0.0.1:8080' . $url,
//                ['headers' => ['X-Litemall-Token' => $this->token]]);
        } else {
            $response1 = $this->post($url, $data, $this->getAuthHeader());
//            $response2 = $client->post('http://127.0.0.1/' . $url, [
//                ['headers' => ['X-Litemall-Token' => $this->token]],
//                'json' => $data
//            ]);
        }
        $response1->assertStatus(200);
        $ret = $response1->getOriginalContent();
        $this->assertEquals('0', $ret['errno']);
//        $content1 = $response1->getContent();
//        $content1 = json_encode(json_decode($content1, true), JSON_UNESCAPED_UNICODE);
//        echo "calmshop => $content1" . PHP_EOL;
//        $content1 = json_decode($content1, true);
//        $content2 = $response2->getBody()->getContents();
//        echo "litemall => $content2" . PHP_EOL;
//        $content2 = json_decode($content2, true);
//        foreach ($ignore as $key) {
//            unset($content1[$key]);
//            unset($content2[$key]);
//        }
//        $this->assertEquals($content2, $content1);
    }

    public function getAuthHeader($username = 'user123', $password = '123456')
    {
        $response = $this->post('/wx/auth/login', ['username' => $username, 'password' => $password]);
        $content  = $response->getOriginalContent();
        Log::debug('authContent', $content);
        $token       = $content['data']['token'];
        $this->token = $token;
        return ['Authorization' => "Bearer {$token}"];
    }
}
