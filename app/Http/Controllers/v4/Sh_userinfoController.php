<?php

namespace App\Http\Controllers\v4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\users\Userinfo;
use App\Http\Controllers\Controller;

class Sh_userinfoController extends Controller
{
    /**
     * 后台用户列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        $page=$request->get('page');
        $limit=$request->get('limit');
        $hid=$request->get('hid');
        $data=DB::table('sh_userinfo')
            ->where(function ($query) use ($hid) {
                if (!empty($hid)) {
                    $query->where('hid', '=', $hid);
                }
            })
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();
        $total=DB::table('sh_userinfo')
            ->where(function ($query) use ($hid) {
                if (!empty($hid)) {
                    $query->where('hid', '=', $hid);
                }
            })
           ->count();
        $data=Userinfo::change($data);
        return response()->json([
            'data'=>$data,
            'total'=>$total
        ]);
    }

    /**
     * 后台对用户操作
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operation(Request $request){
        $operation=$request->get('operation');
        $id=$request->get('id');
        if($operation=='perm'){
            $perm=DB::table('sh_userinfo')->where('id',$id)->value('perm');
            switch ($perm){
                case 1:
                    DB::table('sh_userinfo')->where('id',$id)->update(['perm'=>2]);
                    break;
                case 2:
                    DB::table('sh_userinfo')->where('id',$id)->update(['perm'=>1]);
                    break;
            }

        }elseif($operation=='comp'){
            $comp=DB::table('sh_userinfo')->where('id',$id)->value('comp');
            switch ($comp){
                case 1:
                    DB::table('sh_userinfo')->where('id',$id)->update(['comp'=>2]);
                    break;
                case 2:
                    DB::table('sh_userinfo')->where('id',$id)->update(['comp'=>1]);
                    break;
            }
        }
        return response()->json([
            'msg'=>'success'
        ]);
    }

    /**
     * 前台用户个人数据
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $post){
        $openid=$post->openid;
        $check=Userinfo::check($openid);
        if(!$check){
            return response()->json([
               'msg'=>'未登录'
            ]);
        }
        $data=DB::table('sh_userinfo')
            ->where('openid',$openid)
            ->select('openid','nickname','username','phone','perm','comp')
            ->get();

       $data=Userinfo::change($data);
        return response()->json([
            'data'=>$data
        ]);
    }

    /**
     * 用户信息
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function mes(Request $post){
        $openid=$post->openid;
       $check=Userinfo::check($openid);
        if(!$check){
            return response()->json([
               'msg'=>'未登录'
            ]);
        }
        $id=DB::table('sh_userinfo')
            ->where('openid',$openid)
            ->value('id');
        $data=DB::table('sh_message')
            ->where('uid',$id)
            ->select('mes','date')
            ->get();
        foreach ($data as $value){
            $value->date=date('Y-m-d H:i:s',$value->date);
        }
        return response()->json([
            'data'=>$data
        ]);
    }

    /**
     * 用户所卖物品列表
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function info_goods(Request $post){
        $openid=$post->openid;
        $type=$post->type;
        $uid=DB::table('sh_userinfo')->where('openid',$openid)->value('id');
        $total=DB::table('sh_goods')
            ->where('uid',$uid)
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where('status', '=', $type);
                }
            })
            ->count();
        $data=DB::table('sh_goods')
            ->where('uid',$uid)
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where('status', '=', $type);
                }
            })
            ->select('id','name','status','add_time','sold_time','detail','pic','price')
            ->get();
        foreach($data as $value){
            $value->price=number_format($value->price,2);
        }
       $data=Userinfo::GoodsChange($data);
        return response()->json([
            'data'=>$data,
            'total'=>$total
        ]);
    }

    /**
     * 用户基础信息修改
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function infoEdit(Request $post){
        $openid=$post->openid;
        $username=$post->username;
        $phone=$post->phone;
        $res=DB::table('sh_userinfo')
            ->where('openid',$openid)
            ->update(['username'=>$username,'phone'=>$phone]);
        if($res){
            return response()->json([
                'msg'=>'success'
            ]);
        }else{
            return response()->json([
                'msg'=>'fail'
            ]);
        }
    }

    /**
     * 后台用户列表搜索
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request){
        $text=$request->get('text');
        $page=$request->get('page');
        $limit=$request->get('limit');
        $data=DB::table('sh_userinfo')
            ->where('nickname','like','%'.$text.'%')
            ->orWhere('username','like','%'.$text.'%')
            ->orWhere('phone','like','%'.$text.'%')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();
       $data=Userinfo::change($data);
        return response()->json([
            'data'=>$data
        ]);
    }

    /**
     * 信息录入
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $post){
        $openid=$post->openid;
        $nickname=$post->nickname;
        $username=$post->username;
        $phone=$post->phone;
        $hid=$post->hid;
        $community=$post->community;
        $res=DB::table('sh_userinfo')->insert([
               'openid'=>$openid,'nickname'=>$nickname,'username'=>$username,'hid'=>$hid,'community'=>$community,'phone'=>$phone
            ]);
        if($res){
            return response()->json([
               'msg'=>'success'
            ]);
        }else{
            return response()->json([
               'fail'
            ]);
        }
    }
}
