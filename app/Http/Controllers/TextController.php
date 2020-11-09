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
                $this->text($postArray,$content);
            }
        }elseif ($postArray->MsgType=="text"){
            $msg=$postArray->Content;
            switch ($msg){
                case '你好':
                    $content='亲   你好';
                    $this->text($postArray,$content);
                    break;
                case '天气':
                    $content=$this->getweather();
                    $this->text($postArray,$content);
                    break;
               case '时间';
                    $content=date  ('Y-m-d H:i:s',time());
                    $this->text($postArray,$content);
                    break;
                default;
                $content='啊啊啊啊 亲  你在说什么呢 ';
                $this->text($postArray,$content);
                break;
            }
        }
    }
    public function text($postArray,$content){
//        Log::info('222=============='.$postArray);
//        Log::info('222=============='.$content);
        $toUser   = $postArray->FromUserName;//openid
        $token=$this->token();
        $data="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$toUser."&lang=zh_CN";
        $wetch=file_put_contents('user_wetch',$data);//存文件
        

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
        echo $info;
    }
    //获取天气预报
    public function getweather(){
        $url='http://api.k780.com:88/?app=weather.future&weaid=heze&&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json';
        $weather=file_get_contents($url);
        $weather=json_decode($weather,true);
        if($weather['success']){
            $content = '';
            foreach($weather['result'] as $v){
                $content .= '日期：'.$v['days'].$v['week'].' 当日温度：'.$v['temperature'].' 天气：'.$v['weather'].' 风向：'.$v['wind'];
            }
        }
        Log::info('===='.$content);
        return $content;

    }
    //获取token
    public  function token(){
        $key='wx:access_token';
        //检查是否有token
        $token=Redis::get($key);
        if($token){
//            echo "有缓存";'</br>';
//            echo $token;
        }else{
//            echo"无缓存";'</br>';
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
//        echo $url;exit;
            $response=file_get_contents($url);

            $data=json_decode($response,true);

            $token=$data['access_token'];
            //缓存到redis中  时间为3600

            Redis::set($key,$token);
            Redis::expire($key,3600);
        }

        return $token;
    }
    //测试
    public function tell(){
        $token=$this->token();
//        echo  $token;exit;
        $data="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=&lang=zh_CN";
        echo $data;
    print_r($_GET);
    }
    //测试
    public function tell2(){
        print_r($_POST);
        $aa=file_get_contents("php://input");
        echo $aa;
        $data=json_decode($aa,TRUE);
        print_r($data);
    }
}

