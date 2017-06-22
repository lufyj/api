<?php

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}
 /**
  * 安全过滤函数
  * @param $string
  * @return string
  */
 function safe_replace($string) {
 	$string = str_replace('%20','',$string);
 	$string = str_replace('%27','',$string);
 	$string = str_replace('%2527','',$string);
 	$string = str_replace('*','',$string);
 	$string = str_replace('"','&quot;',$string);
 	$string = str_replace("'",'',$string);
 	$string = str_replace('"','',$string);
 	$string = str_replace(';','',$string);
 	$string = str_replace('<','&lt;',$string);
 	$string = str_replace('>','&gt;',$string);
 	$string = str_replace("{",'',$string);
 	$string = str_replace('}','',$string);
 	$string = str_replace('\\','',$string);
 	return $string;
 } 
 /**
  * 返回经htmlspecialchars处理过的字符串或数组
  * @param $obj 需要处理的字符串或数组
  * @return mixed
  */
 function new_html_special_chars($string) {
 	$encoding = 'utf-8';
 	//if(strtolower(CHARSET)=='gbk') $encoding = 'ISO-8859-15';
 	if(!is_array($string)) return htmlspecialchars($string,ENT_QUOTES,$encoding);
 	foreach($string as $key => $val) $string[$key] = new_html_special_chars($val);
 	return $string;
 } 
 /**
  * [get_filter_param 过滤搜索输入的内容]
  * @param  [type] $arg [description]
  * @return [type]      [description]
  */
function get_filter_param($arg){
 	$arg = safe_replace($arg);
 	$arg = new_html_special_chars(strip_tags($arg));
 	$arg = str_replace('%', '', $arg);	//过滤'%'，用户全文搜索
 	return $arg;
 }
 /**
  * 生成已随机字符串
  * @param unknown $length
  * @return string
  */
 function random_str($length){
 	//生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
 	$arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
 	$str = '';
 	$arr_len = count($arr);
 	for ($i = 0; $i < $length; $i++){
 		$rand = mt_rand(0, $arr_len-1);
 		$str.=$arr[$rand];
 	}
 	return $str;
 }
 /**
  * 系统非常规MD5加密方法
  * @param  string $str 要加密的字符串
  * @return string
  */
 function password_md5($str, $key){
 	$key = empty($k) ? C('WPF_AUTH_KEY') : $key;
 	return '' === $str ? '' : md5(sha1($str) . $key);
 }
 /**
  * 发送短信
  * @param unknown $tel
  * @param unknown $content
  */
 function send_msg($mobile,$content){
 	//获取短信账号密码
 	$token = substr(sha1($mobile.$content),0,24).substr(md5($mobile.$content),0,8);
 	$isok = file_get_contents('http://www.yaoduwang.com/sms.php?tel='.$mobile.'&code='.$content.'&token='.$token);
 	if($isok){
 		return true;
 	}
 	return false;
 }
 
 /**
  * 加密解密函数
  * @param  [type] $string    [description]
  * @param  [type] $operation [description]
  * @param  string $key       [description]
  * @return [type]            [description]
  */
 function encrypt($string,$operation,$key=''){
 	$key=md5($key);
 	$key_length=strlen($key);
 	$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
 	$string_length=strlen($string);
 	$rndkey=$box=array();
 	$result='';
 	for($i=0;$i<=255;$i++){
 		$rndkey[$i]=ord($key[$i%$key_length]);
 		$box[$i]=$i;
 	}
 	for($j=$i=0;$i<256;$i++){
 		$j=($j+$box[$i]+$rndkey[$i])%256;
 		$tmp=$box[$i];
 		$box[$i]=$box[$j];
 		$box[$j]=$tmp;
 	}
 	for($a=$j=$i=0;$i<$string_length;$i++){
 		$a=($a+1)%256;
 		$j=($j+$box[$a])%256;
 		$tmp=$box[$a];
 		$box[$a]=$box[$j];
 		$box[$j]=$tmp;
 		$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
 	}
 	if($operation=='D'){
 		if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
 			return substr($result,8);
 		}else{
 			return'';
 		}
 	}else{
 		return str_replace('=','',base64_encode($result));
 	}
 }
 
 /**
 * 格式化行政区域代码
 * @param  string $code
 * @return string $zoneCode
 * @author jingwei
 */
function formatZoneCode($code){

	$zoneCode=0;
	if(ctype_digit($code)){

		$n=strlen($code);
		switch($n){
			case 2:
				$zoneCode=$code.'0000';
				break;
			case 4:
				$zoneCode=$code.'00';
				break;
			case 6:
				$zoneCode=$code;
				break;
			default:
				$zoneCode=0;
				break;
		}

		return $zoneCode;
	}else{
		return $zoneCode;
	}
}
function region($code){
    $codes = str_split($code,2);
    if($codes[1] == '00'){
        return $codes[0];
    }else if($codes[2] == '00'){
        return $codes[0].$codes[1];
    }else{
        return $codes[0].$codes[1].$codes[2];
    }
}

/**
 * 切割行政区域代码
 * @param  string $code
 * @return array $codeArr
 * @author jingwei
 */
function sliceCode($code){

	if($code==0){
		return array();
	}
	$codeArr=array();
	$codeArr[]=substr($code,0,2);
	$codeArr[]=substr($code,0,4);
	$codeArr[]=substr($code,0,6);

	return $codeArr;
}
/**
*查询ip10秒内访问次数
*/
function getIpNum($realip){
  $ip = S($realip);
  $time = time();
  if($ip && $time  - $ip[$realip]['time'] < 10){
       $data[$realip]['num'] = $ip[$realip]['num'] + 1;
  }else{
       $data[$realip]['num'] = 1 ;
  }
  $data[$realip]['time'] = time();
  //将信息放入缓存
  S($realip,$data);
  //拿取缓存中的数量
  $ips = S($realip);
  $num = $ip[$realip]['num'];
  return $num;
}