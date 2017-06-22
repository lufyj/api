<?php
namespace Api\Controller;
use Think\Controller;
class ExtensionController extends BaseController {
    //下载接口
    public function extension(){
        if(IS_AJAX && IS_POST){
            $map['month']            = date('Ym');
            $extension_code          = I('post.extension_code',0,'intval');
            $map['extension_code']   = $extension_code;

            $num = M('extension_download')->where($map)->getField('num');
            if($num){
                $data['num'] = $num + 1;
                M('extension_download')->where($map)->save($data);
            }else{
                $data['extension_code'] = $extension_code;
                $data['month']          = date('Ym');
                $data['num']            = 1;
                M('extension_download')->add($data);
            }
        }
    }
}
