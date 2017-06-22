/*
纯 JS 表单验证

定义验证数组
var FormData=[
	['txt_aab004','单位名称为2-50个字符！',User,'required'],
];
调用方式
表单事件 onclick="return CheckForm(FormData,'alert')"
如果未传递第二个参数 将会按返回值方式返回
res.status 验证状态
res.msg 错误消息

*/

var regObj = {};
regObj.regList = new Array();
regObj.regList['contacts']	= /^\S{2,20}$/; // 任意非空字符，长度为2-20
regObj.regList['phone']		= /^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/; //手机号码

/* 检查表单
 * @param array $data 待验证数据
 * @param string $alertMe 值为 alert 将直接弹出错误消息
 * 示例数据 ['txt_aab004','单位名称为2-50个字符！','User','required'],
 * 说明 标签ID, 错误消息内容, 验证规则, 必填
*/
function checkForm() {

	var res = {status : true, msg :''};
	var data = arguments[0] ? arguments[0] : []; // 待验证数据
	var alertMe = arguments[1] ? arguments[1] : ''; // 是否直接弹出 alert 将直接弹出

	for (var sn = 0; sn < data.length; sn++) {
		
		var id = data[sn][0]; // 控件 ID
		var msg = data[sn][1]; // 错误消息
		var reg = data[sn][2]; // 正则
		var required = data[sn][3]; // 是否必填
		var value = ''; // 获取到的值

		// 如果前面 false 状态未处理 将跳出
		if (res.status == false)
		{
			break;
		}

		var obj = document.getElementById(id);
		// if (typeof(obj) == "undefined" || obj == null)

		if (!obj) {
			res.status = false;
			res.msg = "id 为 " + id + " 的 HTML 标签不存在";
			break;
		}

		// 判断是否支持 type 属性
		switch (obj.type)
		{
		case 'radio': // 单选框
		case 'checkbox': // 多选框
			var nameObj = document.getElementsByName(id);
			for (var i=0; i<nameObj.length; i++) {
				if (nameObj[i].checked) {
					value = value + nameObj[i].value + ',';
				}
			}
			break;

		case 'text': // 文本框
		case 'password': // 密码框
		case 'hidden': // 隐藏域
			value = obj.value;
			break;
		case 'file': // 文件上传验证
			res = regObj.regList['fileUpload2'](id);
			continue;
			break;
		default:
			// 其他 textarea 从实体中获取内容
			if (typeof(obj.selectedIndex) !== "undefined")
			{
				// select
				value = obj.options[obj.selectedIndex].value;
				// 空值 0 值 都置为空
				value = (value == '' || value == 0) ? '' : value;
			} else {
				// textarea
				value = obj.value;
			}

		}

		// 判断正则是否是函数 如果是则执行它
		// 来自 js中判断是否为一个函数 https://segmentfault.com/a/1190000002763235
		if (Object.prototype.toString.call(regObj.regList[reg])=== '[object Function]')
		{
			res = regObj.regList[reg](value);

		} else {
			if (typeof(required) !== "undefined" && required !== null) {

				if (value == '')
				{
				res.status = false;
				res.msg = msg;
				break;
				}
			}

			// 判断规则是否存在
			if (!regObj.regList[reg])
			{
				res.status = false;
				res.msg = reg + " 验证规则不存在";
				break;
			}

			// 进行正则验证
			if (!regObj.regList[reg].test(value)) {
				res.status = false;
				res.msg = msg;
				obj.focus();
				break;
			}
		}

	}
	// 运行完返回错误 还是直接弹出
	if (alertMe == 'alert')
	{
		if (res.status == false)
		{
			$.custom(res.msg);
		}
		return res.status;

	} else {
		return res;
	}

}