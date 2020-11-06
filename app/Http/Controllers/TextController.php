<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}
