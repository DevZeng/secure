<?php

namespace App\Http\Controllers\v4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class Sh_FigureController extends Controller
{
    /**
     * 轮播图
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        $data=DB::table('sh_figure')->where('status',1)->pluck('pic');
        return response()->json([
            'data'=>$data
        ]);
    }

    /**
     * 轮播图新增
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request){
        $data=$request->all();
        $res=DB::table('sh_figure')->insert($data);
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
     * 状态修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status_click(Request $request){
        $id=$request->get('id');
        $status=DB::table('sh_figure')->where('id',$id)->value('status');
        if($status==1){
            DB::table('sh_figure')->where('id',$id)->update(['status'=>2]);
        }else{
            DB::table('sh_figure')->where('id',$id)->update(['status'=>1]);
        }
        return response()->json([
           'msg'=>'success'
        ]);
    }

    /**
     * 轮播图修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request){
       $data=$request->all();
        DB::table('sh_figure')
            ->where('id',$data['id'])
            ->update(['pic'=>$data['pic'],'status'=>$data['status']]);
        return response()->json([
           'msg'=>'success'
        ]);
    }

    /**
     * 轮播图删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request){
       $id=$request->get('id');
        $res=DB::table('sh_figure')->where('id',$id)->delete();
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
