<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Log;
use App\models\WeachModel;
use GuzzleHttp\Client;
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
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
            //使用guzzle发起get请求
            $client=new Client();
            //['verify'=>false]   不加这个会报ssl错误  因为默认是true
            $response=$client->request ('GET',$url,['verify'=>false]);  //发起请求并接受响应
            $json_str=$response->getBody();    //服务器的响应数据
            echo $json_str;
            //接收数据
            $xml_str=file_get_contents("php://input");
            //记录日记
            file_put_contents('wx_event.log',$xml_str);
            //把xml转换为php的对象或者数组
            //调用关注回复
            $this->sub();
            //调用自定义菜单
//            $this->custom();
            echo "";
        }else{
            echo '';
        }
    }
    //关注回复
    public function sub($postArray){
        $toUser= $postArray->FromUserName;//openid
        $postStr = file_get_contents("php://input");
//        Log::info("====".$postStr);
        $postArray=simplexml_load_string($postStr);
//        Log::info('=============='.$postArray);
        //evnet  判断是不是推送事件
        if($postArray->MsgType=="event"){
            if($postArray->Event=="subscribe"){
                $WeachModelInfo=WeachModel::where('openid',$toUser['openid'])->first();
                if(!empty($WeachModelInfo)) {
                    $content = "欢迎回来";
                }

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
    //关注回复  判断再次回来
    public function text($postArray,$content){
//        Log::info('222=============='.$postArray);
//        Log::info('222=============='.$content);
        $toUser= $postArray->FromUserName;//openid
//        echo $toUser;exit;
        $token=$this->token();
        $data="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$toUser."&lang=zh_CN";
        file_put_contents('user_wetch',$data);//存文件
        $wetch=file_get_contents($data);
        $json=json_decode($wetch,true);
//        file_put_contents('user_wetch',$data,'FILE_APPEND');//存文件
//        die;
        $data=[
            'openid'=>$toUser,
            'nickname'=>$json['nickname'],
            'sex'=>$json['sex'],
            'city'=>$json['city'],
            'country'=>$json['country'],
            'province'=>$json['province'],
            'language'=>$json['language'],
            'subscribe_time'=>$json['subscribe_time'],
        ];
        $weachInfo=WeachModel::insert($data);

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
        $url='http://api.k780.com:88/?app=weather.future&weaid=beijing&&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json';
        $weather=file_get_contents($url);
        $weather=json_decode($weather,true);
//        dd($weather);
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

            //使用guzzle发起get请求
            $client=new Client();
            //['verify'=>false]   不加这个会报ssl错误  因为默认是true
            $response=$client->request ('GET',$url,['verify'=>false]);  //发起请求并接受响应
            $json_str=$response->getBody();    //服务器的响应数据
//            echo $json_str;
            $data=json_decode($json_str,true);

            $token=$data['access_token'];
            //缓存到redis中  时间为3600
            Redis::set($key,$token);
            Redis::expire($key,3600);
        }

        return $token;
    }
    //GET测试
    public function guzzle(){
//        echo __METHOD__;
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
        //使用guzzle发起get请求
        $client=new Client();
        //['verify'=>false]   不加这个会报ssl错误  因为默认是true
        $response=$client->request ('GET',$url,['verify'=>false]);  //发起请求并接受响应
        $json_str=$response->getBody();    //服务器的响应数据
    echo $json_str;
    }
    //POST 上传素材
    public function guzzle2(){
        $access_token=$this->token();
        $type='image';
        $url='https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type;
//        echo $url;exit;
        $client=new Client();
        //['verify'=>false]   不加这个会报ssl错误  因为默认是true
        $response=$client->request ('POST',$url,[
            'verify'=>false,
            'multipart'=>[
                [
                    'name'=>'media',
                    'contents'=>fopen('timg.jpg','r'),
                ], //上传的文件路径
            ]
        ]);  //发起请求并接受响应
        $data=$response->getBody();
        echo $data;
    }
    //自定义菜单
    public function custom(){
        $menu = '{
             "button":[
             {
                  "type":"click",
                  "name":"今日歌曲",
                  "key":"V1001_TODAY_MUSIC"
              },
              {
              "type":"click",
                  "name":"天气",
                  "key":$this->getweather(),
              },
              {
                   "name":"优惠活动",
                   "sub_button":[
                   {
                       "type":"view",
                       "name":"今日领劵",
                       "url":"http://www.soso.com/"
                    },
                    {
                       "type":"click",
                       "name":"我的优惠券",
                       "key":"V1001_GOOD"
                    }]
               }]
         }';

        $access_token=$this->token();
        $url=' https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token.'';
//        echo $url;
        $this->url($url,$menu);
//    echo $menu;
    }
    //自定义菜单封装的
    public function url($url,$menu){
        //1.初始化
        $ch = curl_init($url);
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//设置提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//设置返回值字符串
        curl_setopt($ch,CURLOPT_POST,1);//设置提交方式为post
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //3.执行
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
//        dd($output);
        return $output;
    }
}

