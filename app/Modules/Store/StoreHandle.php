<?php
/**
 * Created by PhpStorm.
 * User: zeng
 * Date: 2018/6/21
 * Time: 下午2:47
 */

namespace App\Modules\Store;


use App\Modules\Product\Model\Product;
use App\Modules\Product\Model\Stock;
use App\Modules\Store\Model\Express;
use App\Modules\Store\Model\ExpressConfig;
use App\Modules\Store\Model\Store;
use App\Modules\Store\Model\StoreAmount;
use App\Modules\Store\Model\StoreCategory;
use App\Modules\Store\Model\StoreExpress;
use App\Modules\Store\Model\StoreWithdraw;
use function GuzzleHttp\Psr7\uri_for;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait StoreHandle
{
    public function addStoreCategory($id,$data)
    {
        $category = $id?StoreCategory::find($id):new StoreCategory();
        foreach ($data as $key=>$value){
            $category->$key = $value;
        }
        if ($category->save()){
            return true;
        }
        return false;
    }
    public function countCategory($title,$level=0,$parent_id=0,$id=0)
    {
        $db = DB::table('store_categories');
        if ($level){
            $db->where('parent_id','=',$parent_id);
        }
        if ($title){
            $db->where('title','like','%'.$title.'%');
        }
        if ($id){
            $db->where('id','!=',$id);
        }
        return $db->count();
    }

    public function getStoreCategories($page,$limit,$level=0,$parent_id=0,$hot=0,$title='')
    {
        $db = DB::table('store_categories');
        if ($level){
            $db->where('parent_id','=',$parent_id);
        }
        if ($title){
            $db->where('title','like','%'.$title.'%');
        }
        if ($hot){
            $db->where('hot','=',1);
        }
        $count = $db->count();
        $data = $db->limit($limit)->offset(($page-1)*$limit)->get();
        return [
            'count'=>$count,
            'data'=>$data
        ];
    }
    public function formatStoreCategories(&$categories)
    {
        if (empty($categories)){
            return [];
        }
        foreach ($categories as $category){
            $category->parent = StoreCategory::find($category->parent_id);
            $category->parent_id = intval($category->parent_id);
        }
        return $categories;
    }
    public function getStoreCategoriesTree()
    {
        $categories = StoreCategory::all()->toArray();
        $parents = array_merge(array_filter($categories,function ($item){
            return $item['parent_id'] == 0;
        }));
        for ($i=0;$i<count($parents);$i++){
            $id = $parents[$i]['id'];
            $parents[$i]['childs'] = array_merge(array_filter($categories,function ($item) use ($id){
                return $item['parent_id'] == $id;
            }));
        }
        return $parents;
    }
    public function delStoreCategory($id)
    {
        $category = StoreCategory::findOrFail($id);
        if ($category->delete()) {
            return true;
        }
        return false;
    }

    public function addExpress($id = 0, $store_id, $title, $code)
    {
        if ($id) {
            $express = Express::find($id);
        } else {
            $express = new Express();
        }
        $express->store_id = $store_id;
        $express->title = $title;
        $express->code = $code;
        if ($express->save()) {
            return true;
        }
        return false;
    }

    public function getExpresses($store_id = 0, $page, $limit, $title = '', $code)
    {
        $db = DB::table('expresses');
        if ($store_id) {
            $db->where('store_id', '=', $store_id);
        }
        if ($title) {
            $db->where('title', 'like', '%' . $title . '%');
        }
        if ($code) {
            $db->where('code', 'like', '%' . $code . '%');
        }
        $count = $db->count();
        $data = $db->get();
        return [
            'count' => $count,
            'data' => $data
        ];
    }

    public function delExpress($id)
    {
        $express = Express::findOrFail($id);
        if ($express->store_id != getStoreId()) {
            return false;
        }
        if ($express->delete()) {
            return true;
        }
        return false;
    }

    public function addStore($user_id, $data)
    {
        $store = Store::where('user_id','=',$user_id)->first();
        if (empty($store)){
            $store = new Store();
            $store->user_id = $user_id;
        }
        foreach ($data as $key => $value) {
            $store->$key = $value;
        }
        if ($store->save()) {
            return true;
        }
        return false;
    }

    public function getUserStoreCount($user_id, $id)
    {
        return Store::where('user_id', '=', $user_id)->where('id', '!=', $id)->count();
    }

    public function getUserStore($user_id)
    {
        return Store::where('user_id', '=', $user_id)->first();
    }
    public function getStoreById($id)
    {
        return Store::find($id);
    }
    public function getStores($name = '', $page, $limit)
    {
        $db = DB::table('stores');
        if ($name) {
            $db->where('name', 'like', $name);
        }
        $count = $db->count();
        $data = $db->orderBy('id', 'DESC')->limit($limit)->offset(($page - 1) * $limit)->get();
        return [
            'data' => $data,
            'count' => $count
        ];
    }

    public function getStoresByGrid($category_id=0,$minLat,$minLon,$maxLat,$maxLon)
    {
        $db = Store::whereBetween('lat',[$minLat,$maxLat])->whereBetween('lon',[$minLon,$maxLon]);
        if ($category_id){
            $db->where('category_id','=',$category_id);
        }
        return $db->get()->toArray();
    }
    public function sortStores(&$stores,$lat,$lon)
    {
        if (empty($stores)){
            return [];
        }
        $count = count($stores);
        for($i=0;$i<$count;$i++){
            $stores[$i]['lat'] = sprintf('%.7f',$stores[$i]['lat']);
            $stores[$i]['lon'] = sprintf('%.7f',$stores[$i]['lon']);
            $stores[$i]['distance'] = sprintf('%.3f',calculateDistance($stores[$i]['lat'],$stores[$i]['lon'],$lat,$lon));
        }
        array_multisort(array_column($stores,'distance'),SORT_DESC,$stores);
        return $stores;
    }

    public function getStoresId($name)
    {
        $db = DB::table('stores');
        if ($name) {
            $db->where('name', 'like', '%' . $name . '%');
        }
        return $db->pluck('id')->toArray();
    }

    public function getStoresIdByStockId($idArray)
    {
        $productId = Stock::whereIn('id', $idArray)->pluck('product_id')->toArray();
        $storesId = Product::whereIn('id', $productId)->pluck('store_id')->toArray();
        return array_unique($storesId);
    }

    public function addStoreExpressConfig($store_id, $businessId, $apiKey)
    {
        $config = ExpressConfig::where('store_id', '=', $store_id)->first();
        if (empty($config)) {
            $config = new ExpressConfig();
            $config->store_id = $store_id;
        }
        $config->business_id = $businessId;
        $config->api_key = $apiKey;
        if ($config->save()) {
            return true;
        }
        return false;
    }

    public function getStoreExpressConfig($store_id)
    {
        $config = ExpressConfig::where('store_id', '=', $store_id)->first();
        if (empty($config)) {
            $config = new ExpressConfig();
        }
        return $config;
    }
    public function setStoreExpress($store_id,$express_id,$price)
    {
        $storeExpress = StoreExpress::where('store_id','=',$store_id)->first();
        if (empty($storeExpress)){
            $storeExpress = new StoreExpress();
            $storeExpress->store_id = $store_id;
        }
        $storeExpress->express_id = $express_id;
        $storeExpress->price = $price;
        if ($storeExpress->save()){
            return true;
        }
        return false;
    }
    public function getStoreExpress($store_id)
    {
        return StoreExpress::where('store_id','=',$store_id)->first();
    }
    public function formatStoreExpress(&$data)
    {
        if (empty($data)){
            return null;
        }
        $data->express = Express::find($data->express_id);
        return $data;
    }
    public function addStoreAmount($store_id,$amount)
    {
        $storeAmount = StoreAmount::where('store_id','=',$store_id)->first();
        if (empty($storeAmount)){
            $storeAmount = new StoreAmount();
            $storeAmount->store_id = $store_id;
            $storeAmount->amount = 0;
        }
        $storeAmount->amount += $amount;
        $storeAmount->available += $amount;
        if ($storeAmount->save()){
            return true;
        }
        return false;
    }
    public function setStoreAmount($store_id,$amount)
    {
        $storeAmount = StoreAmount::where('store_id','=',$store_id)->first();
        if (empty($storeAmount)){
            $storeAmount = new StoreAmount();
            $storeAmount->store_id = $store_id;
        }
        $storeAmount->amount = $amount;
        $storeAmount->available = $amount;
        if ($storeAmount->save()){
            return true;
        }
        return false;
    }
    public function setStoreAmountAvailable($store_id,$amount)
    {
        $storeAmount = StoreAmount::where('store_id','=',$store_id)->first();
        if (empty($storeAmount)){
            return false;
        }
        $storeAmount->available -= $amount;
        if ($storeAmount->save()){
            return true;
        }
        return false;
    }
    public function getStoreAmount($store_id)
    {
        return StoreAmount::where('store_id','=',$store_id)->first();
    }
    public function countStoreWithdraw($store_id,$state=1)
    {
        return StoreWithdraw::where('store_id','=',$store_id)->where('state','=',$state)->sum('price');
    }
    public function addStoreWithdraw($id,$data)
    {
        if ($id){
            $withdraw = StoreWithdraw::find($id);
        }else{
            $withdraw = new StoreWithdraw();
        }
        foreach ($data as $key=>$value){
            $withdraw->$key = $value;
        }
        if ($withdraw->save()){
            return true;
        }
        return false;
    }
    public function getStoreWithdraws($page=1,$limit=10,$store_id=0,$state=0)
    {
        $db = DB::table('store_withdraws');
        if ($store_id){
            $db->where('store_id','=',$store_id);
        }
        if ($state){
            $db->where('state','=',$state);
        }
        $count = $db->count();
        $data = $db->limit($limit)->offset(($page-1)*$limit)->orderBy('id','DESC')->get();
        return [
            'data'=>$data,
            'count'=>$count
        ];
    }
    public function getStoreWithdraw($id)
    {
        return StoreWithdraw::find($id);
    }
    public function formatStoreWithdraws(&$withdraws)
    {
        if (empty($withdraws)){
            return [];
        }
        foreach ($withdraws as $withdraw){
            $withdraw->store = Store::find($withdraw->store_id);
        }
        return $withdraws;
    }
}