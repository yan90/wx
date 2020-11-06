<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class TextController extends Controller
{
    public  function text1(){
//        $list=DB::table('p_users'p->limit(3)->get();
//        dd($list);

    }
    public function checkSignature(Request $request)
    {
        $echostr=$request->echostr;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo $echostr;
        }else{
            return false;
        }
    }
    public  function token(){
        $key='wx:access_token';
        //检查是否有token
        $token=Redis::get($key);
        if($token){
            echo "有缓存";'</br>';
            echo $token;
        }else{
        echo"无缓存";'</br>';
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
//        echo $url;exit;
        $response=file_get_contents($url);


        $data=json_decode($response,true);
        $token=$data['access_token'];
        //缓存到redis中  时间为3600

        Redis::set($key,$token);
        Redis::expire($key,3600);
        }

        echo "access_token:".$token;
    }


}
