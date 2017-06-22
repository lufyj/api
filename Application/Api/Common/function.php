<?php

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
 * 判断来自哪个客户端
 * @return string
 */
function get_device_type(){
	//全部变成小写字母
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$type = 0;
	//分别进行判断
	if(strpos($agent, 'iphone') || strpos($agent, 'ipad')){
		$type = 2;
	}else if(strpos($agent, 'android')){
		$type = 1;
	}
	return $type;
}
//去掉省市后面追加的00
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
 * 前台公共库文件
 * 主要定义前台公共函数库
 */
/**
 * [RemoveXSS 移除拼缀字符 避免 xss]
 * @param [type] $val [description]
 */
function removeXss($val) {
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
		$val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
	}

	$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);
	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(�{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
			$val = preg_replace($pattern, $replacement, $val);
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}


/**
 * get传数据
 * @param unknown $str
 * @return mixed
 */
function get_curl($str){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $str);
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec($ch);
	return $output;
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
	//对内容进行编码
	/* 
	 * 
	$uid = C('SMS_ACCOUNT');
	$pwd = C('SMS_PASSWORD');
	$content = urlencode($content);
	$url="http://182.92.185.251:8890/mtPort/mt?phonelist=". $mobile ."&content=". $content ."&pwd=". md5($pwd) ."&uid=". $uid;	
	$result = get_curl($url);
	$xml = simplexml_load_string($result);
	$code = (int) $xml->CODE;//在做数据比较时，注意要先强制转换
	//并且不为空
	if($code == 0){
		return true;
	}
	else{
		return false;
	}	 */
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
/* 根据用户id生成token */
function getToken($id){
	return md5($id.NOW_TIME.mt_rand(10, 100).'ydw');
}
/*
*验证用户输入过来的验证码
*/
function extensionCode($code){
	if(strlen($code) == 6){
		$num_split = str_split($code,'5');
		$num_splits = str_split($num_split[0],'1');
		$num_total = $num_splits[0] + $num_splits[1] + $num_splits[2] +$num_splits[3] + $num_splits[4];
		if($num_total < 10){
			if($num_total == '9'){
				$total = '0';
			}else{
				$total = $num_total;
			}
		}else{
			$nums_split = str_split($num_total,'1');
			$num_total  = $nums_split[0] + $nums_split[1];
			if($num_total > 8){
				$num_total = $num_total - 9;
			}
			$total = $num_total;
		}
		if($total == $num_split[1]){
			return true;
		}else{
			return false;
		}


	}else{
		return false;
	}
}
/* 将手机中间四位隐藏 */
function maskPhone($phone){
	return substr_replace($phone, '****', 3, 4);
}