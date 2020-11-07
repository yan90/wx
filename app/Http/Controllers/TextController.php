<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Log;

class TextController extends Controller
{

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
            //接收数据
            $xml_str=file_get_contents("php://input");
            //记录日记
            file_put_contents('wx_event.log',$xml_str);
            //把xml转换为php的对象或者数组
            $this->sub();
            echo "";
        }else{
            echo '';
        }

    }
    //关注回复
    public function sub(){
        $postStr = file_get_contents("php://input");
//        Log::info("====".$postStr);
        $postArray=simplexml_load_string($postStr);
//        Log::info('=============='.$postArray);
        if($postArray->MsgType=="event"){
            if($postArray->Event=="subscribe"){
                $content="你好，欢迎关注";
//                Log::info('111=============='.$postArray);
                $ret = $this->text($postArray,$content);
                echo $ret;
            }
        }
    }
    public function text($postArray,$content){
//        Log::info('222=============='.$postArray);
//        Log::info('222=============='.$content);
        $toUser   = $postArray->FromUserName;
                Log::info('222=============='.$toUser);

        $fromUser = $postArray->ToUserName;
        $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
        $info = sprintf($template, $toUser, $fromUser, time(), 'text', $content);
        return $info;
    }
    //获取token
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
