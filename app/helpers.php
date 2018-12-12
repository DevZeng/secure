<?php
/**
 * Created by PhpStorm.
 * User: zeng
 * Date: 2018/5/29
 * Time: 下午2:52
 */

/**
 * 返回json响应
 */
if (!function_exists('jsonResponse')){
    function jsonResponse($param,$code=200){
        return response()->json($param,$code);
    }
}
/**
 * 返回视图响应
 */
if (!function_exists('viewResponse')){
    function viewResponse($view,$param){
        return view($view,$param);
    }
}
/**
 * 返回随机字符串
 */
if (!function_exists('createNonceStr')){
    function CreateNonceStr($length = 10){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}
/**
 * 设置redis缓存数据
 *
 */
if (!function_exists('setRedisData')){
    function setRedisData($key,$value,$time=0){
        \Illuminate\Support\Facades\Redis::set($key,$value);
        if ($time!=0){
            \Illuminate\Support\Facades\Redis::expire($key,$time);
        }
    }
}
/**
 * 获取redis缓存数据
 */
if (!function_exists('getRedisData')){
    function getRedisData($key,$default=0){
        $data = \Illuminate\Support\Facades\Redis::get($key);
        if (!$data){
            return $default;
        }
        return $data;
    }
}
if (!function_exists('getUserData')){
    function getUserData($data,$key){
        $data = json_decode($data);
        return $data->$key;
    }
}
/**
 *  返回校验消息
 */
if (!function_exists('getRequestMessage')){
    function getRequestMessage($key){
        $message = config('message.'.$key);
        return $message;
    }
}
if (!function_exists('setStoreId')){
    function setStoreId($store_id)
    {
        session(['storeId'=>$store_id]);
    }
}
if (!function_exists('getStoreId')){
    function getStoreId()
    {
        return session('storeId');
    }
}
if (!function_exists('checkPermission')){
    function checkPermission($uid,$permission)
    {
        $role = \App\Modules\Role\Model\RoleUser::where('user_id','=',$uid)->pluck('role_id')->first();
        $permissionId = \App\Modules\Role\Model\Permission::where('name','=',$permission)->pluck('id')->first();
        $rolePermission = \App\Modules\Role\Model\RolePermission::where('role_id','=',$role)->where('permission_id','=',$permissionId)->first();
        if (empty($rolePermission)){
            return false;
        }
        return true;
    }
}
if (!function_exists('getWxXcx')){
    function getWxXcx(){
        $config = \App\Modules\System\Model\TxConfig::first();
        $wxxcx = new \App\Libraries\Wxxcx($config->app_id,$config->app_secret);
        return $wxxcx;
    }
}
if (!function_exists('getWxPay')) {
    function getWxPay($open_id=''){
        $config = \App\Modules\System\Model\TxConfig::first();
        $wxpay = new \App\Libraries\WxPay($config->app_id,$config->mch_id,$config->api_key,$open_id);
        return $wxpay;
    }
}
if (!function_exists('getWxNotify')) {
    function getWxNotify(){
        $config = \App\Modules\System\Model\TxConfig::first();
        $wxNotify = new \App\Libraries\WxNotify($config->app_id,$config->app_secret);
        return $wxNotify;
    }
}
if (!function_exists('filterEmoji')){
    function filterEmoji($str)
    {
        $str = str_replace(PHP_EOL, '', $str);
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }
}

if (!function_exists('getAround')){
    function getAround($lat,$lon,$radius){
        $PI = 3.14159265;

        $latitude = $lat;
        $longitude = $lon;

        $degree = (24901*1609)/360.0;
        $radiusMile = $radius;

        $dpmLat = 1/$degree;
        $radiusLat = $dpmLat*$radiusMile;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;

        $mpdLng = $degree*cos($latitude * ($PI/180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng*$radiusMile;
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;
        return [
            'minLat'=>round($minLat,7),
            'maxLat'=>round($maxLat,7),
            'minLng'=>round($minLng,7),
            'maxLng'=>round($maxLng,7),
        ];
    }
}
if (!function_exists('calculateDistance')){
    function calculateDistance($lat1,$lon1,$lat2,$lon2,$radius=6378.135){
        if ($lat1==$lat2&&$lon1==$lon2){
            return 0.001;
        }
        $rad = doubleval(M_PI/180.0);
        $lat1 = doubleval($lat1) * $rad;
        $lon1 = doubleval($lon1) * $rad;
        $lat2 = doubleval($lat2) * $rad;
        $lon2 = doubleval($lon2) * $rad;

        $theta = $lon2 - $lon1;
        $dist = acos(sin($lat1) * sin($lat2) +
            cos($lat1) * cos($lat2) * cos($theta));
        if($dist < 0) {
            $dist += M_PI;
        }
        // 单位为 千米
        return $dist = $dist * $radius;
    }
}

/**
 * 图片压缩
 */
if(!function_exists('ImageCompression')) {
    function ImageCompression($photo, $ext, $radio)
    {

        $name = uniqid() . '.' . $ext;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $dst_im = imagecreatefromjpeg($photo);
                imagejpeg($dst_im, '../public/uploads/pic/' . $name, $radio);
                break;
            case 'png':
                $dst_im = imagecreatefrompng($photo);
                imagepng($dst_im, '../public/uploads/pic/' . $name, $radio);
                break;
        }
        imagedestroy($dst_im);
        return 'uploads/pic/' . $name;
    }
}
