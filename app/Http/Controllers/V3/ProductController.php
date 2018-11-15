<?php

namespace App\Http\Controllers\V3;

use App\Modules\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ProductController extends Controller
{
    //
    public function __construct()
    {
        $this->handle = new User();
    }
    public function getProductsByType()
    {
        $type = Input::get('type');
        $page = Input::get('page',1);
        $limit = Input::get('limit',10);
        $name = Input::get('name');
        $data = $this->handle->getProductsApi($name,$type,$page,$limit);
        $this->handle->formatProductApi($data['data']);
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }
}
