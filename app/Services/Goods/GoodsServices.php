<?php

namespace App\Services\Goods;

use App\Inputs\GoodsListInput;
use App\Models\Goods\Collect;
use App\Models\Goods\Comment;
use App\Models\Goods\Footprint;
use App\Models\Goods\Goods;
use App\Services\BaseServices;
use App\Services\Users\UserServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class GoodsServices extends BaseServices
{

    public function queryOnSale()
    {
        return Goods::query()->where(['is_on_sale' => 1, 'deleted' => 0])->count('id');
    }

    public function listGoods(GoodsListInput $input)
    {
        $query = $this->getQueryByGoodsFilter($input);
        if (!empty($input->categoryId)) {
            $query = $query->where('category_id', $input->categoryId);
        }
        return $query->orderBy($input->sort, $input->order)->paginate($input->limit,
            ['id', 'name', 'brief', 'pic_url', 'is_hot', 'is_new', 'counter_price', 'retail_price'], 'page', $input->page);

    }


    public function listL2Gategory(GoodsListInput $input)
    {

        $query = $this->getQueryByGoodsFilter($input);
        $categoryIds = $query->select('category_id')->pluck('category_id')->toArray();
        return CatalogServices::getInstance()->getL2ListByIds($categoryIds);
    }


    private function getQueryByGoodsFilter(GoodsListInput $input)
    {
        $query = Goods::query()->where('is_on_sale', 1)->where('deleted', 0);
        if (!empty($input->brandId)) {
            $query = $query->where('brand_id', $input->brandId);
        }
        if (!empty($input->isNew)) {
            $query = $query->where('is_new', $input->isNew);
        }
        if (!empty($input->isHot)) {
            $query = $query->where('is_hot', $input->isHot);
        }

        if (!empty($input->keyword)) {
            $query = $query->where(function (Builder $query) use ($input) {
                $query->where('keywords', 'like', "%$input->keyword%")
                    ->orWhere('name', 'like', "%$input->keyword%");
            });
        }

        return $query;
    }


    public function findById($id)
    {
        return Goods::query()->where('deleted', 0)->find($id);
    }


    public function getCollectCount($userId, $type = 0, $gid)
    {
        return Collect::query()
            ->where([
                'user_id' => $userId,
                'type' => $type,
                'value_id' => $gid,
                'deleted' => 0,
            ])
            ->count('id');
    }

    public function getCommentByGid($valueId, $page = 1, $limit = 2)
    {
        return Comment::query()
            ->where('value_id', $valueId)->where('type', 0)->where('deleted', 0)
            ->orderByDesc('add_time')
            ->paginate($limit, ['*'], 'page', $page);

    }

    public function getCommentWithUserInfo($goodId, $page = 1, $limit = 2)
    {
        $comments = $this->getCommentByGid($goodId);
        $userIds = Arr::pluck($comments->items(), 'user_id');
        $userIds = array_unique($userIds);
        $users = UserServices::getInstance()->getUsersByIds($userIds)->keyBy('id');
        $data = collect($comments->items())->map(function (Comment $comment) use ($users) {
            $user = $users->get($comment->user_id);
            $comment = $comment->toArray();
            $comment['picList'] = $comment['picUrls'];
            $comment = Arr::only($comment, ['id', 'addTime', 'content', 'adminContent', 'picList']);
            $comment['nickname'] = $user->nickname;
            $comment['avatar'] = $user->avatar;

            return $comment;
        });
        return ['count' => $comments->total(), 'data' => $data];

//        return $comments;

    }

    public function saveFootprint($userId, $goodsId)
    {
        $footprint = new Footprint();
        $footprint->fill([
            'user_id' => $userId,
            'goods_id' => $goodsId
        ]);
        $footprint->save();
        return $footprint;
    }

}

