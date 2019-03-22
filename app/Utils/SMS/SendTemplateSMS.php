<?php

namespace App\Utils\SMS;

class SendTemplateSMS
{
  //主帐号
  private $accountSid='8a216da86904c06001691965f15709a1';

  //主帐号Token
  private $accountToken='8ed7f9d7c88648cc8377a7744bf0d67a';

  //应用Id
  private $appId='8a216da86904c06001691965f1ab09a8';

  //请求地址，格式如下，不需要写https://
  private $serverIP='sandboxapp.cloopen.com';

  //请求端口
  private $serverPort='8883';

  //REST版本号
  private $softVersion='2013-12-26';

  /**
    * 发送模板短信
    * @param to 手机号码集合,用英文逗号分开
    * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
    * @param $tempId 模板Id
    */
  public function sendTemplateSMS($to,$datas,$tempId)
  {
       $res = array();

       // 初始化REST SDK
       $rest = new CCPRestSDK($this->serverIP,$this->serverPort,$this->softVersion);
       $rest->setAccount($this->accountSid,$this->accountToken);
       $rest->setAppId($this->appId);

       // 发送模板短信
      //  echo "Sending TemplateSMS to $to <br/>";
       $result = $rest->sendTemplateSMS($to,$datas,$tempId);
       if($result == NULL ) {
           $res['status'] = 3;
           $res['message'] = 'result error!';
       }
       if($result->statusCode != 0) {
           $res['status'] = $result->statusCode;
           $res['message'] = $result->statusMsg;
       }else{
           $res['status'] = 0;
           $res['message'] = '发送成功';
       }

       return $res;
  }
}

//sendTemplateSMS("18576437523", array(1234, 5), 1);
