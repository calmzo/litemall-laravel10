<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GoodsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
//    public function test_example(): void
//    {
//        $this->assertLitemallApiGet('wx/goods/detail?id=1009009');
//    }

    public function testCount(): void
    {
        $this->assertLitemallApiGet('wx/goods/count');
    }

    public function testCategory(): void
    {
        $this->assertLitemallApiGet('wx/goods/category?id=1008009');
    }

    public function testlist(): void
    {
        $this->assertLitemallApiGet('wx/goods/list?categoryId=1008009');
        $this->assertLitemallApiGet('wx/goods/list?brandId=1001000');
        $this->assertLitemallApiGet('wx/goods/list?keyword=四件套');
        $this->assertLitemallApiGet('wx/goods/list?isNew=1');
        $this->assertLitemallApiGet('wx/goods/list?isHot=1');
        $this->assertLitemallApiGet('wx/goods/list?page=2&limit=5');
    }

    public function testDetail(): void
    {
        $this->assertLitemallApiGet('wx/goods/detail?id=1009009');
    }
}
