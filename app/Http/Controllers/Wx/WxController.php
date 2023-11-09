<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Utils\CodeResponse;
use App\ValidateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WxController extends Controller
{
    use ValidateRequest;

    protected $only;
    protected $except;

    /**
     * WxController constructor.
     */
    public function __construct()
    {
        $options = [];
        if (!is_null($this->only)) {
            $options['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $options['except'] = $this->except;
        }
        $this->middleware('auth:wx', $options);
    }

    protected function codeReturn(array $codeResponse, $data = null, $info = '')
    {
        list($errno, $errmsg) = $codeResponse;
        $ret = ['errno' => $errno];
        if (!is_null($data)) {
            $ret['data'] = $data;
        }
        $ret['errmsg'] = $info ?: $errmsg;
        return response()->json($ret);
    }

    protected function success($data = null)
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    protected function fail(array $codeResponse = CodeResponse::FAIL, $info = '')
    {
        return $this->codeReturn($codeResponse, null, $info);
    }

    protected function successPaginate($page)
    {
        return $this->success($this->paginate($page));
    }

    protected function paginate($page, $list = null)
    {
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page' => $page->total() == 0 ? 0 : $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->total() == 0 ? 0 : $page->lastPage(),
                'list' => $list ?? $page->items()
            ];
        }
        if ($page instanceof Collection) {
            $page = $page->toArray();
        }
        if (!is_array($page)) {
            return $page;
        }
        $total = count($page);
        return [
            'total' => $total,
            'page' => 1,
            'limit' => $total,
            'pages' => 1,
            'list' => $page
        ];

        return $page;

    }

    public function user()
    {
        return Auth::guard('wx')->user();
    }


    public function isLogin()
    {
        return !is_null($this->user());
    }

    public function userId()
    {
        return $this->user()->getAuthIdentifier();
    }
}
