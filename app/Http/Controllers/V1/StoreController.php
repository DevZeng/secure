<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\ExpressPost;
use App\Http\Requests\StorePost;
use App\Modules\User;
use function GuzzleHttp\Psr7\uri_for;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class StoreController extends Controller
{
    //
    private $handle;
    public function __construct()
    {
        $this->handle = new User();
    }
    public function addStoreCategory(Request $post)
    {
        $id = $post->id?$post->id:0;
        $parent_id = $post->parent_id?$post->parent_id:0;
        $icon = $post->icon?$post->icon:'';
        if ($this->handle->countCategory($post->title,1,0,$id)!=0){
            throw new \Exception('该类目已存在!');
        }
        $data = [
            'title'=>$post->title,
            'parent_id'=>$parent_id,
            'icon'=>$icon
        ];
        if ($this->handle->addStoreCategory($id,$data)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
    }
    public function getStoreCategories()
    {
        $page = Input::get('page',1);
        $limit = Input::get('limit',10);
        $level = Input::get('level',0);
        $parent_id = Input::get('parent_id',0);
        $hot = Input::get('hot',0);
        $title = Input::get('title');
        $data = $this->handle->getStoreCategories($page,$limit,$level,$parent_id,$hot,$title);
        $this->handle->formatStoreCategories($data['data']);
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }
    public function getStoreCategoriesTree()
    {
        $data = $this->handle->getStoreCategoriesTree();
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }
    public function setHotStoreCategory()
    {
        $id = Input::get('id');
        $category = $this->handle->getStoreCategory($id);
        $hot = $category->hot==0?1:0;
        $data = [
            'hot'=>$hot
        ];
        if ($this->handle->addStoreCategory($id,$data)){
            return jsonResponse([
                'msg'=>'ok',
                'data'=>$data
            ]);
        }
    }
    public function deletesStoreCategory()
    {
        $id = Input::get('id');
        if ($this->handle->delStoreCategory($id)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
        return jsonResponse([
            'msg'=>'删除失败！'
        ],400);
    }
    public function test(Request $request)
    {
        $path = $request->file('image')->store('images','public');
//        $path = Storage::putFile('avatars', $request->file('avatar'));
        return $path;
    }
    public function getSettleApplies()
    {
        $page = Input::get('page',1);
        $limit = Input::get('limit',10);
        $data = $this->handle->getSettleApplies($page,$limit);
        return $data;
    }
    public function checkSettleApply()
    {
        $id = Input::get('id');
        $state = Input::get('state',2);
        $result = $this->handle->checkSettleApply($id,$state);
        if ($this->handle->checkDefaultRole()){
            return jsonResponse([
                'msg'=>'没有默认角色！'
            ],400);
        }
        if (!$result){
            return jsonResponse([
                'msg'=>'操作失败！'
            ],400);
        }else{
            if ($state==1){
                $apply = $this->handle->getSettleApplyById($result);
                $user = new \App\User();
                $user->username = $apply->phone;
                $user->phone = $apply->phone;
                $user->password = bcrypt('123456');
                $user->save();
                $role = $this->handle->getDefaultRole();
                $this->handle->addRoleUser($role->id,$user->id);
            }
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }

    }
    public function addStore(Request $post)
    {
        $user_id = $this->handle->addUser(0,[
            'username'=>$post->phone,
            'phone'=>$post->phone,
            'password'=>bcrypt('123456')
        ],0);
        $data = [
            'name'=>$post->name,
            'manager'=>$post->manager,
            'phone'=>$post->phone,
            'lat'=>$post->lat,
            'lon'=>$post->lon,
            'address'=>$post->address,
            'document'=>$post->document,
            'category_id'=>$post->category_id,
            'notify_id'=>$post->notify_id,
            'user_id'=>$user_id
        ];
        if ($this->handle->addStore(0,$data)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
    }
    public function getUserStore()
    {
        $store = $this->handle->getUserStore(Auth::id());
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$store
        ]);
    }
    public function editUserStore(Request $post)
    {
        $store = $this->handle->getUserStore(Auth::id());
        if ($store->user_id != Auth::id()){
            throw new MethodNotAllowedException('操作不允许！');
        }
        $data = [
            'name'=>$post->name
        ];
        if ($this->handle->addStore($store->id,$data)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
    }
    public function getStoreExpresses()
    {
        $page = Input::get('page',1);
        $limit = Input::get('limit',10);
        $title = Input::get('title','');
        $code = Input::get('code','');
        $data = $this->handle->getExpresses(getStoreId(),$page,$limit,$title,$code);
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }
    public function delExpress()
    {
        $id = Input::get('id');
        if ($this->handle->delExpress($id)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
        return jsonResponse([
            'msg'=>'操作失败！'
        ],400);
    }
    public function addExpress(ExpressPost $post)
    {
        $id = $post->id?$post->id:0;
        if ($this->handle->addExpress($id,getStoreId(),$post->title,$post->code)){
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
        return jsonResponse([
            'msg'=>'操作失败！'
        ]);
    }
    public function addExpressConfig(Request $post)
    {
        if ($this->handle->addStoreExpressConfig(getStoreId(),$post->businessId,$post->apiKey)) {
            return jsonResponse([
                'msg'=>'ok'
            ]);
        }
        return jsonResponse([
            'msg'=>'系统错误！'
        ],400);
    }
    public function getExpressConfig()
    {
        $data = $this->handle->getStoreExpressConfig(getStoreId());
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }
    public function getStores()
    {
        $page = Input::get('page',1);
        $limit = Input::get('limit',10);
        $name = Input::get('name');
        $type = Input::get('type2');
        $data = $this->handle->getStores($name,$page,$limit,0,$type);
        return jsonResponse([
            'msg'=>'ok',
            'data'=>$data
        ]);
    }

}
