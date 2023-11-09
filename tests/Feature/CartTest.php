<?php

namespace Tests\Feature;

use App\Models\Goods\GoodsProduct;
use App\Models\User;
use App\Services\Order\CartServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    private $authHeader;

    /**
     * @var GoodsProduct $production
     */
    private $production;

    public function setUp(): void
    {
        parent::setUp();
        $this->production = GoodsProduct::factory()->create(['number' => 10]);
        $this->authHeader = $this->getAuthHeader($this->user->username, '123456');
    }

    public function testAdd(): void
    {
        $resp = $this->post('wx/cart/add', [
            'goodsId' => 0,
            'productId' => 0,
            'number' => 1,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 402, "errmsg" => "参数值不对"]);


        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 11,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 711, "errmsg" => "商品库存不足!"]);


        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 2,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 0, "data" => "2", "errmsg" => "成功"]);

        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 3,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 0, "data" => "5", "errmsg" => "成功"]);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->production->id, $this->production->goods_id);
        $this->assertEquals(5, $cart->number);

        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 6,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 711, "errmsg" => "商品库存不足!"]);


    }

    public function testUpdate()
    {
        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 2,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 0, "data" => "2", "errmsg" => "成功"]);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->production->id, $this->production->goods_id);

        $resp = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 6,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 0, "errmsg" => "成功"]);

        $resp = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 11,
        ], $this->authHeader);
        $resp->assertJson(['errno' => 711, 'errmsg' => '商品库存不足!']);

        $resp = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 0,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 402]);
    }

    public function testDelete()
    {
        $resp = $this->post('wx/cart/add', [
            'goodsId' => $this->production->goods_id,
            'productId' => $this->production->id,
            'number' => 2,
        ], $this->authHeader);
        $resp->assertJson(["errno" => 0, "data" => "2", "errmsg" => "成功"]);
        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->production->id, $this->production->goods_id);
        $this->assertNotNull($cart);

        $resp = $this->post('wx/cart/delete', [
            'productIds' => [$this->production->id]
        ], $this->authHeader);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->production->id, $this->production->goods_id);
        $this->assertNull($cart);

        $resp = $this->post('wx/cart/delete', [
            'productIds' => []
        ], $this->authHeader);
        $resp->assertJson(["errno" => 402, "errmsg" => "参数值不对"]);
    }
}
