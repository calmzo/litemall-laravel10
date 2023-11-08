<?php

namespace App\Services\Goods;

use App\Inputs\GoodsListInput;
use App\Models\Collect;
use App\Models\Comment;
use App\Models\FootPrint;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsAttribute;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Issue;
use App\Services\BaseServices;
use App\Services\User\UserServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;


class GoodsServices extends BaseServices
{


    public function listL2Gategory(GoodsListInput $input)
    {

        $query = $this->getGoodsQuery($input);
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

    /**
     * 获取新发商品
     * @param $limit
     * @param int $offset
     * @return \App\Models\BaseModel[]|\App\Models\Goods\Category[]|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|\think\Collection
     */
    public function getNewGoods($limit, $offset = 0)
    {

        $conditions = [
            'is_on_sale' => 1,
            'is_new'     => 1
        ];
        return $this->getGoodsByConditions($conditions, $offset, $limit);
    }

    /**
     * @param $conditions
     * @param $offset
     * @param $limit
     * @param  string  $sort
     * @param  string  $order
     * @param  string[]  $columns
     * @return Goods[]|Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     * 根据条件获取商品数据
     */
    private function getGoodsByConditions(
        $conditions,
        $offset,
        $limit,
        $sort = 'add_time',
        $order = 'desc',
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price']
    ) {
        return Goods::query()->where($conditions)->offset($offset)->limit($limit)->orderBy($sort, $order)->get($columns);
    }

    /**
     * @param $offset
     * @param $limit
     * @return Goods[]|Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     * 获取热门商品
     */
    public function getHotGoods($limit, $offset = 0)
    {
        $conditions = [
            'is_hot'     => 1,
            'is_on_sale' => 1
        ];
        return $this->getGoodsByConditions($conditions, $offset, $limit);
    }


    /**
     * @param $productId
     * @param $num
     * @return int
     * 减库存
     */
    public function reduceStock($productId, $num)
    {
        return GoodsProduct::query()->where('id', $productId)->where('number', '>=', $num)->decrement('number', $num);
    }

    /**
     * @param $productId
     * @param $num
     * @return int
     * 加库存 使用乐观锁
     * @throws Throwable
     */
    public function addStock($productId, $num)
    {
        /** @var GoodsProduct $product */
        $product         = $this->getGoodsProductById($productId);
        $product->number = $product->number + $num;
        return $product->cas();
    }


    /**
     * @param $ids
     * @return Goods[]|Builder[]|Collection
     * 根据商品的id,获取商品的列表
     */
    public function getGoodsListByIds($ids)
    {
        return Goods::query()->whereIn('id', $ids)->get();
    }

    /**
     * @return Builder[]|Collection
     * 获取商品的问题
     */
    public function getGoodsIssue()
    {
        return Issue::query()->get();
    }

    /**
     * @param $id
     * @return Builder[]|Collection
     * 获取商品的产品
     */
    public function getGoodsProducts($id)
    {
        return GoodsProduct::query()->where('goods_id', $id)->get();
    }

    /**
     * @param $id
     * @return GoodsProduct|GoodsProduct[]|Builder|Builder[]|Collection|Model|null
     * 根据产品的ID获取产品信息
     */
    public function getGoodsProductById($id)
    {
        return GoodsProduct::query()->find($id);
    }

    /**
     * @param  array  $ids
     * @return GoodsProduct[]|Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     * 批量获取产品
     */
    public function getGoodsProductsByIds(array $ids)
    {
        return GoodsProduct::query()->whereIn('id', $ids)->get();
    }

    /**
     * @param $id
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     * 获取产品的规格
     */
    public function getGoodsSpecification($id)
    {
        $spec = GoodsSpecification::query()->where('goods_id', $id)->get();
        $spec = $spec->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v];
        })->values();
    }

    public function getGoodsAttributesList($id)
    {
        return GoodsAttribute::query()->where('goods_id', $id)->get();
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null|Goods
     * 获取商品
     */
    public function getGoods($id)
    {
        return Goods::query()->find($id);
    }

    /**
     * @return int
     * 获取在售商品的数量
     */
    public function countGoodsOnSales()
    {
        return Goods::query()->where('is_on_sale', 1)->count('id');
    }

    /**
     * @param \App\Input\GoodsListInput $input
     * @return mixed
     * 获取商品的列表
     */
    public function goodsLists(GoodsListInput $input)
    {

        $query = Goods::query()->select([
            'id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'
        ])->where('is_on_sale', 1);

        $query = $this->getGoodsQuery($query, $input->keyword, $input->brandId, $input->isNew, $input->isHot);

        if (!empty($input->categoryId)) {
            $query = $query->where('category_id', $input->categoryId);
        }

        if (!empty($input->sort) && !empty($input->order)) {
            $query = $query->orderBy($input->sort, $input->order);
        }

        return $query->paginate($input->limit, ['*'], 'page', $input->page);
    }

    /**
     * @param  GoodsListInput  $input
     * @return mixed
     * 获取商品分类ID的数据
     */
    public function getCatIds(GoodsListInput $input)
    {
        $query = Goods::query()->where('is_on_sale', 1);
        $query = $this->getGoodsQuery($query, $input->keyword, $input->brandId, $input->isNew, $input->isHot);
        return $query->select(['category_id'])->pluck('category_id')->toArray();
    }

    private function getGoodsQuery($query, $keywords, $brandId, $isNew, $isHot)
    {
        if (!empty($brandId)) {
            $query = $query->where('brand_id', $brandId);
        }

        if (!is_null($isNew)) {
            $query = $query->where('is_new', $isNew);
        }

        if (!is_null($isHot)) {
            $query = $query->where('is_hot', $isHot);
        }

        if (!empty($keywords)) {
            $query->Where('keywords', 'like', "%{$keywords}%")->orWhere('name', 'like', "%{$keywords}%");
        }
        return $query;
    }
}

