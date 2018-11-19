<?php

namespace App\Modules\users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Userinfo extends Model
{
    public static function check($openid){
        if(!$openid){
            return false;
        }
        $id=DB::table('sh_userinfo')
            ->where('openid',$openid)
            ->value('id');
        if(!$id){
            return false;
        }
        return true;
    }

    public static function change($data)
    {
        $len=sizeof($data);
        for($i=0;$i<$len;$i++) {
            switch ($data[$i]->perm) {
                case 1:
                    $data[$i]->perm = '允许用户出售';
                    break;
                case 2:
                    $data[$i]->perm = '禁止用户出售';
                    break;
            }

            switch ($data[$i]->comp) {
                case 1:
                    $data[$i]->comp = '允许用户投诉';
                    break;
                case 2:
                    $data[$i]->comp = '禁止用户投诉';
                    break;
            }
        }

        return $data;
    }

    public static function GoodsChange($data){
        foreach($data as $value){
            $value->add_time=date('Y-m-d H:i:s',$value->add_time);
            if($value->sold_time==0){
                $value->sold_time='';
            }else{
                $value->sold_time=date('Y-m-d H:i:s',$value->sold_time);
            }
            switch($value->status){
                case 1:
                    $value->status='待售中';
                    break;
                case 2:
                    $value->status='已售出';
                    break;
                case 3:
                    $value->status='待审核';
                    break;
            }
        }
        return $data;
    }
}
