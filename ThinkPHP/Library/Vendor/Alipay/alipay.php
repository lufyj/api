<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.4
 * 修改日期：2016-03*08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*****************
 
 *如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 *1、开发文档中心（https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.KvddfJ&treeId=62&articleId=103740&docType=1）
 *2、商户帮助中心（https://cshall.alipay.com/enterprise/help_detail.htm?help_id=473888）
 *3、支持中心（https://support.open.alipay.com/alipay/support/index.htm）

 *如果想使用扩展功能,请按文档要求,自行添加到parameter数组即可。
 **********************************************
 */
require_once("lib/alipay_submit.class.php");
class Alipay{
	public static function pay($alipayConfig,$args){

		$parameter = array(
			"service"       => $alipayConfig['service'],
			"partner"       => $alipayConfig['partner'],
			"seller_id"  => $alipayConfig['seller_id'],
			"payment_type"	=> $alipayConfig['payment_type'],
			"notify_url"	=> $alipayConfig['notify_url'],
			"return_url"	=> $alipayConfig['return_url'],

			"anti_phishing_key"=>$alipayConfig['anti_phishing_key'],
			"exter_invoke_ip"=>$alipayConfig['exter_invoke_ip'],
			"out_trade_no"	=> $args['out_trade_no'],
			"subject"	=> $args['subject'],
			"total_fee"	=> $args['total_fee'],
			"body"	=> $args['body'],
			"_input_charset"	=> trim(strtolower($alipayConfig['input_charset']))
			//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
			//如"参数名"=>"参数值"

		);

	//建立请求
		$alipaySubmit = new AlipaySubmit($alipayConfig);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		echo $html_text;
	}
}