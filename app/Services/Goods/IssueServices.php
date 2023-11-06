<?php

namespace App\Services\Goods;

use App\Models\Goods\Issue;
use App\Services\BaseServices;

class IssueServices extends BaseServices
{
    public function querySelective(
        $question,
        $page,
        $limit,
        $sort = 'id',
        $order = 'asc'
    ) {
        $query = Issue::query()->where('deleted', 0);
        if (!empty($question)) {
            $query = $query->where('question', 'like', "%{$question}%");
        }
        return $query->orderBy($sort, $order)->forPage($page, $limit)->get();
    }

}

