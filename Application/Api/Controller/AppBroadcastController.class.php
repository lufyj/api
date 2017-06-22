<?php
namespace Api\Controller;
use Think\Controller;
class AppBroadcastController extends BaseController {
    //App端播报  安卓版本1.4
    public function Broadcast(){
        if($this->client_type == 1){
            $access_ios         = C('ACCESS_IOS');
            $access_android     = C('ACCESS_ANDROID');
            $data['status'] = 1;
            $list = M('realtime_message')->where($data)->field('content,tid,type')->select();
            foreach ($list as $k => $v) {
                if($v['type'] > 0){
                    //安卓
                    $addroid  = explode('@@',$access_android[$v['type']]);
                    $list[$k]['access_android'] = $addroid[0];
                    $list[$k]['addroid_id']     = $addroid[1];
                    //ios 
                    $ios      = explode('@@',$access_ios[$v['type']]);
                    $list[$k]['access_ios']     = $ios[0];
                    $list[$k]['ios_id']         = $ios[1];
                }else{
                    $list[$k]['access_android'] =  $list[$k]['access_ios'] =  $list[$k]['addroid_id'] =  $list[$k]['ios_id']  = '';
                }
            }
            if($list !== false){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
             $this->ajaxDie(43);
        }
    }
}