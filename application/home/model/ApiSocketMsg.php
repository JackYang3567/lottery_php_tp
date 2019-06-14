<?php
namespace app\home\model;
use think\Model;

class ApiSocketMsg extends Model
{
    public static function pushMsg($pushdata, $to){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://127.0.0.1:2121");
        curl_setopt($ch,CURLOPT_POST,false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $data=array( "type"=>"publish",
                     "content"=>json_encode($pushdata,JSON_UNESCAPED_UNICODE),
                     "to"=>$to);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $strRes=curl_exec($ch); 
        curl_close($ch);
        $arrResponse=json_decode($strRes,true);

        if($arrResponse['status']==0)
        {
        /**错误处理*/
          return iconv('UTF-8','GBK',$arrResponse['err_msg']);
        }
        /** tinyurl */
        return;
    }
}
