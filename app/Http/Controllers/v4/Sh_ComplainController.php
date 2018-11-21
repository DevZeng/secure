<?php

namespace App\Http\Controllers\v4;

use App\Modules\users\Userinfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class Sh_ComplainController extends Controller
{
    public function complain(Request $post){
        $openid=$post->openid;
        $gid=$post->gid;
        $text=$post->text;
        $now=time();
        $check=Userinfo::check($openid);
        if($check){
            return response()->json([
                'msg'=>'未登录'
            ]);
        }
        $comp=DB::table('sh_userinfo')->where('openid',$openid)->value('comp');
        if($comp==2){
            return response()->json([
               'msg'=>'已被管理员禁止投诉'
            ]);
        }
        $cuid=DB::table('sh_userinfo')->where('openid',$openid)->value('id');
        $uid=DB::table('sh_goods')->where('id',$gid)->value('uid');
        $res=DB::table('sh_complain')->insert([
            'uid'=>$uid,'cuid'=>$cuid ,'reason'=>$text,'gid'=>$gid,'add_time'=>$now
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

    public function complain_list(Request $request){
        $page=$request->get('page');
        $limit=$request->get('limit');
        $data=DB::table('sh_complain')
            ->leftJoin('sh_goods','sh_goods.id','=','sh_complain.gid')
            ->leftJoin('sh_userinfo','sh_userinfo.id','=','sh_complain.uid')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->select('sh_complain.id','sh_complain.uid','sh_complain.cuid','sh_complain.reason','sh_userinfo.hid',
                'sh_complain.add_time','sh_complain.gid','sh_goods.name','sh_complain.status','sh_userinfo.nickname')
            ->get();
        foreach ($data as $value){
            $value->add_time=date('Y-m-d H:i:s',$value->add_time);
            $value->cnickname=DB::table('sh_userinfo')->where('id',$value->cuid)->value('nickname');
            switch ($value->status){
                case 1:
                    $value->status='未处理';
                    break;
                case 2:
                    $value->status='已处理';
                    break;
            }
        }
        return response()->json([
            'data'=>$data
        ]);
    }

    public function deal_complain(Request $request){
        $id=$request->get('id');
        $data=DB::table('sh_complain')->where('id',$id)->select('uid','cuid','gid')->get();
        $data=$data[0];
        $goods_name=DB::table('sh_goods')->where('id',$data['gid'])->value('name');
        $res=DB::table('sh_complain')->where('id',$id)->update(['status'=>2]);
        $now=time();
        DB::table('sh_message')->insert([
           ['uid'=>$data['uid'],'mes'=>'商品：'.$goods_name.'被投诉，已被管理员处理','date'=>$now],
            ['uid'=>$data['cuid'],'mes'=>'您投诉的商品：'.$goods_name.'，已被管理员处理','date'=>$now]
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

    public function complain_del(Request $request){
        $id=$request->get('id');
        $res=DB::table('sh_complain')->where('id',$id)->delete();
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
}
