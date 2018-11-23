<?php

namespace App\Http\Controllers\v4;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class Sh_systemController extends Controller
{
    private $radio=75;

    /**
     * 本地压缩上传
     * @param Request $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $post){
        if (!$post->hasFile('pic')){
            return response()->json([
                'msg'=>'空文件'
            ]);
        }
        $pic=$post->file('pic');
        if($pic->isValid()){
            $name=uniqid();
            $ext=$pic->getClientOriginalExtension();
            $allow =  [
                'jpg',
                'png',
                'jpeg',
            ];
            if (!in_array(strtolower($ext),$allow)){
                return response()->json([
                    'msg'=>'不支持的文件格式'
                ]);
            }
            $ext=strtolower($ext);
            $name=$name.'.'.$ext;
            $path = $pic->storeAs('pic', $name);
            $path1='../public/uploads/'.$path;
            $path=ImageCompression($path1,$ext,$this->radio);
            unlink($path1);
            return response()->json([
                'msg'=>'success',
                'path'=>$path
            ]);
        }else{
            return response()->json([
                'msg'=>'上传失败！'
            ]);
        }
    }
}
