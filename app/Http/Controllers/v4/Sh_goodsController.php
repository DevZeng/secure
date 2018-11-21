<?php

namespace App\Http\Controllers\v4;

use App\Models\Userinfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Runner\Exception;
use App\Http\Controllers\Controller;

class Sh_goodsController extends Controller
{
    /**
     * 后台商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_index(Request $request){
        $page=$request->get('page');
        $limit=$request->get('limit');
        $hid=$request->get('hid');
        $cid=$request->get('cid');
        $text=$request->get('text');
        $total=DB::table('sh_goods')
            ->leftJoin('sh_userinfo','sh_goods.uid','=','sh_userinfo.id')
            ->where(function ($query) use ($hid) {
                if (!empty($hid)) {
                    $query->where('sh_goods.hid', '=', $hid);
                }
            })
            ->where(function ($query) use ($cid) {
                if (!empty($cid)) {
                    $query->where('sh_goods.cid', '=', $cid);
                }
            })
            ->where(function ($query) use ($text) {
                if (!empty($text)) {
                    $query ->where('sh_userinfo.username','like','%'.$text.'%')->orWhere('sh_goods.name','like','%'.$text.'%');
                }
            })
            ->count();
        $data=DB::table('sh_goods')
            ->leftJoin('sh_userinfo','sh_goods.uid','=','sh_userinfo.id')
            ->where(function ($query) use ($hid) {
                if (!empty($hid)) {
                    $query->where('sh_goods.hid', '=', $hid);
                }
            })
            ->where(function ($query) use ($cid) {
                if (!empty($cid)) {
                    $query->where('sh_goods.cid', '=', $cid);
                }
            })
            ->where(function ($query) use ($text) {
                if (!empty($text)) {
                    $query ->where('sh_userinfo.username','like','%'.$text.'%')->orWhere('sh_goods.name','like','%'.$text.'%');
                }
            })
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->select('sh_goods.id','sh_goods.name','sh_goods.status','sh_goods.add_time','sh_userinfo.community',
                'sh_goods.sold_time','sh_userinfo.nickname','sh_userinfo.username','sh_goods.pic','sh_goods.price')
            ->get();
        $data=Userinfo::GoodsChange($data);
        return response()->json([
            'data'=>$data,
            'total'=>$total
        ]);
    }

    /**
     * 后台分类获得
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_classify(Request $request){
        $page=$request->get('page');
        $limit=$request->get('limit');
        $data=DB::table('sh_classify')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();
        $total=DB::table('sh_classify')->count();
        return response()->json([
            'data'=>$data,
            'total'=>$total
        ]);
    }

    /**
     * 商品相册获得
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pic(Request $request){
        $id=$request->get('gid');
        $data=DB::table('sh_pic')
            ->where('gid',$id)
            ->pluck('pic');
        return response()->json([
           'data'=>$data
        ]);
    }

    /**
     * 前台获得
     * @return \Illuminate\Http\JsonResponse
     */
    public function home_classify(){
        $data=DB::table('sh_classify')->where('state',1)->select('id','name','pic')->get();
        return response()->json([
            'data'=>$data
        ]);
    }

//    public function test(){
//        for($i=0;$i<11;$i++) {
//            $res = DB::table('sh_goods')
//                ->insert([
//                    'name'=>'紫砂茶壶','uid'=>1,'status'=>1,'add_time'=>time(),'detail'=>'良品紫砂茶壶',
//                    'hid'=>1,'pic'=>'http://img1.imgtn.bdimg.com/it/u=3765471279,3259829906&fm=26&gp=0.jpg',
//                    'cid'=>2,'price'=>'100','portrait'=>'http://img1.imgtn.bdimg.com/it/u=3765471279,3259829906&fm=26&gp=0.jpg'
//                ]);
//        }
//        if($res){
//            return response()->json([
//                'msg'=>'success'
//            ]);
//        }else{
//            return response()->json([
//                'msg'=>'fail'
//            ]);
//        }
//    }

    /**
     * 分类增加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function classify_add(Request $request){
        $name=$request->get('name');
        $pic=$request->get('pic');
        $res=DB::table('sh_classify')->insert([
            'name'=>$name,'pic'=>$pic
        ]);
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
     * 分类修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function classfy_edit(Request $request){
        $name=$request->get('name');
        $pic=$request->get('pic');
        $id=$request->get('id');
        $res=DB::table('sh_classify')->where('id',$id)->update([
            'name'=>$name,'pic'=>$pic
        ]);
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
     * 分类删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function classfy_del(Request $request){
        $id=$request->get('id');
        $res=DB::table('sh_classify')->where('id',$id)->delete();
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
     * 分类上下架
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function classfy_click(Request $request){
        $id=$request->get('id');
        $state=DB::table('sh_classify')->where('id',$id)->value('state');
        switch($state){
            case 1:
                DB::table('sh_classify')->where('id',$id)->update(['state'=>0]);
                break;
            case 0:
                $check=DB::table('sh_classify')->where('state',1)->select('id')->count();
                if($check==10){
                    return response()->json([
                       'msg'=>'上架分类已满10个，无法上架新分类'
                    ]);
                }else{
                    DB::table('sh_classify')->where('id',$id)->update(['state'=>1]);
                }
            break;
        }
        return response()->json([
            'msg'=>'success'
        ]);
    }

    /**
     * 前台商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function home_index(Request $request){
        $page=$request->get('page');
        $limit=$request->get('limit');
        $hid=$request->get('hid');
        $cid=$request->get('cid');
        $search=$request->get('search');
//        $i=0;
//        $j=0;

        $data=DB::table('sh_goods')
            ->where('status',1)
            ->where('hid',$hid)
            ->where(function ($query) use ($cid) {
                if (!empty($cid)) {
                    $query->where('cid', '=', $cid);
                }
            })
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name','like','%'.$search.'%');
                }
            })
            ->orderby('add_time','DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->select('id','pv','name','uid','add_time','pic','nickname','portrait','price')
            ->get();

        foreach($data as $value){
            $value->add_time=date('Y-m-d H:i:s',$value->add_time);
            $value->price=number_format($value->price,2);

        }

        return response()->json([
            'data'=>$data
        ]);
    }

    /**
     * 获得商品卖家信息
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserMes(Request $post){
        $openid=$post->openid;
        $gid=$post->gid;
        $check=Userinfo::check($openid);
        if(!$check){
            return response()->json([
                'msg'=>'未登录'
            ]);
        }
        $pv=DB::table('sh_goods')->where('id',$gid)->value('pv');
        $pv++;
        DB::table('sh_goods')->where('id',$gid)->update(['pv'=>$pv]);
        $data=DB::table('sh_goods')
            ->leftJoin('sh_userinfo','sh_userinfo.id','=','sh_goods.uid')
            ->where('sh_goods.id',$gid)
            ->select('sh_goods.name','sh_goods.add_time','sh_goods.detail','sh_goods.pic',
                'sh_goods.pv','sh_goods.price','sh_userinfo.username','sh_goods.portrait','sh_userinfo.phone')
            ->get();

        $data=$data[0];
        $data->add_time=date('Y-m-d',$data->add_time);
        $data->price=number_format($data->price,2);
        $data->photo=DB::table('sh_pic')->where('gid',$gid)->pluck('pic');
        return response()->json([
            'data'=>$data
        ]);
    }


    /**
     * 前台新增
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $post){
        $openid=$post->openid;
        $hid=$post->hid;
        $name=$post->name;
        $pic=$post->pic;
        $photo=$post->photo;
        $detail=$post->detail;
        $cid=$post->cid;
        $price=$post->price;
        $portrait=$post->portrait;
        $nickname=$post->nickname;
        $tag=uniqid();
        $now=time();
        $check=Userinfo::check($openid);
        if(!$check){
            return response()->json([
                'msg'=>'未登录'
            ]);
        }
        $perm=DB::table('sh_userinfo')->where('openid',$openid)->value('perm');
        if($perm==2){
            return response()->json([
                'msg'=>'已被管理员禁止出售商品'
            ]);
        }
        $id=DB::table('sh_userinfo')->where('openid',$openid)->value('id');
        $res=DB::table('sh_goods')->insert(['name'=>$name,'uid'=>$id,'cid'=>$cid,'nickname'=>$nickname,'pic'=>$pic,
            'add_time'=>$now,'detail'=>$detail,'tag'=>$tag,'hid'=>$hid,'price'=>$price,'portrait'=>$portrait]);

        $gid=DB::table('sh_goods')->where('tag',$tag)->value('id');

        if($photo){
            $len=sizeof($photo);
            DB::beginTransaction();
            try {
                for ($i = 0; $i < $len; $i++) {
                    DB::table('sh_pic')->insert(['pic'=>$photo[$i],'gid'=>$gid]);
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'msg'=>'相册新增失败',
                    'error'=>$e
                ]);
            }
        }

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
     * 后台审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request){
        $gid=$request->get('gid');
        $res=DB::table('sh_goods')->where('id',$gid)->update(['status'=>1]);
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
     * 前台修改
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $post){
        $openid=$post->openid;
        $check=Userinfo::check($openid);
        if($check){
            return response()->json([
                'msg'=>'未登录'
            ]);
        }
        $gid=$post->gid;
        $name=$post->name;
        $detail=$post->detail;
        $pic=$post->pic;
        $photo=$post->photo;
        $cid=$post->cid;
        $price=$post->price;
        $res=DB::table('sh_goods')->where('id',$gid)->update(['name'=>$name,'detail'=>$detail,'pic'=>$pic,'cid'=>$cid,'price'=>$price]);
        if($photo){
            DB::table('sh_pic')->where('gid',$gid)->delete();
            $len=sizeof($photo);
            DB::beginTransaction();
            try {
                for ($i = 0; $i < $len; $i++) {
                    DB::table('sh_pic')->insert(['pic'=>$photo[$i],'gid'=>$gid]);
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'msg'=>'相册修改失败',
                    'error'=>$e
                ]);
            }
        }

        return response()->json([
            'msg'=>'success'
        ]);
    }

    /**
     * 前台售出，在售
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sold(Request $request){
        $gid=$request->get('gid');
        $status=DB::table('sh_goods')->where('id',$gid)->value('status');
        switch($status){
            case 1:
                $res=DB::table('sh_goods')->where('id',$gid)->update(['status'=>2]);
                break;
            case 2:
                $res=DB::table('sh_goods')->where('id',$gid)->update(['status'=>1]);
                break;
        }
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
     * 前台删除
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $post){
        $openid=$post->openid;
        $gid=$post->get('gid');
        $check=Userinfo::check($openid);
        if(!$check){
            return response()->json([
                'msg'=>'未登录'
            ]);
        }
        $res=DB::table('sh_goods')->where('id',$gid)->where('uid',$check)->delete();
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
     * 后台删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_del(Request $request){
        $gid=$request->get('gid');
        $now=time();
        $uid=DB::table('sh_goods')->where('id',$gid)->value('uid');
        $name=DB::table('sh_goods')->where('id',$gid)->value('name');
        $res=DB::table('sh_goods')->where('id',$gid)->delete();
        $res1=DB::table('sh_message')->insert([
            'uid'=>$uid,'date'=>$now,'mes'=>'商品:'.$name.'或涉及违规，已被管理员删除！'
        ]);

        if($res && $res1){
            return response()->json([
                'msg'=>'success'
            ]);
        }else{
            return response()->json([
                'msg'=>'fail'
            ]);
        }
    }


}
