<?php

namespace App\Services\Promotion;

use App\Models\Promotion\Groupon;
use App\Services\BaseServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\AbstractFont;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GrouponServices extends BaseServices
{

    public function getGrouponListByLimit($limit = 5, $offset = 0, $order = 'desc', $sort = 'add_time')
    {
        return Groupon::query()->offset($offset)->limit($limit)->orderBy($sort, $order)->get();
    }

    /**
     * 创建图片示例
     *
     * 1、获取链接，创建二维码
     * 2、合成图片
     * 3、保存图片，返回图片地址
     * @return string
     */
    public function createGrouponShareImage()
    {
        $shareUrl = \Route('redirectShareurl', ['type' => 'groupon', 'id' => '111']);
        $qrcode = QrCode::format('png')->margin(1)->size(290)->generate($shareUrl);

//        $goodsImage = Image::make($rules->pic_url)->resize(660,660);
        $image = Image::make(resource_path('image/back_groupon.png'))
            ->insert($qrcode, 'top-left', 448, 767)
            ->text('商品', 65, 875, function (AbstractFont $font) {
                $font->color(array(167, 136, 69));
                $font->size(28);
//                $font->file(resource_path('ttf/msyh.ttf'));
            });
        $filePath = "groupon/".Carbon::now()->toDateString().'/'.Str::random().'.png';
        Storage::disk('public')->put($filePath, $image->encode());
        $url = Storage::url($filePath);
        return $url;
    }


}

