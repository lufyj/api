<?php
namespace Api\Controller;
use Think\Controller;
class AppSearchController extends BaseController {
    public function hot_search(){
        if($this->client_type == 1){
            //查询出热门搜索中 搜索度前10的药材
            $goods_hot = M('searchHot')->order('goods_hot desc')->limit(10)->getField('goods_name',true);
            if($goods_hot){
                $this->ajaxDie(1,$goods_hot);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    // 新搜索
    public function search(){
         $goods_name = clearXSS(I('post.goods_name'));
         if(preg_match("/^[a-zA-Z\s]+$/",$goods_name)){
                $goods_name = strtolower($goods_name);
                $fg =  new  \Org\Util\fg;
                $goods = $fg->py_fg($goods_name);
                $goods_py = $goods_name;
                $sql = 'select goods_name,alias_name,goods_py from ydw_search_hot where MATCH(goods_py) AGAINST("'.$goods.'"IN BOOLEAN MODE) order by goods_hot desc limit 10;';
            }else{
                //将用户传过来的汉字 按词分割(空格)
                $goods = mb_str_split($goods_name);
                $sql = 'select goods_name,alias_name,goods_py from ydw_search_hot where MATCH(goods_name) AGAINST("'.$goods.'" IN BOOLEAN MODE) order by goods_hot desc limit 10;';
            }
            $list = M()->query($sql); 
            if($list){
                foreach ($list as $k => $v) {
                //两种情况  一种是有别名  一种是无别名
                //有别名
                if($v['alias_name']){
                        $data['goods_name'] = array('not in', $v['goods_name']);
                        $data['alias_name'] = $v['alias_name'];
                        $alias_name = M('search_hot')->where($data)->limit(1)->getField('goods_name',true);
                        array_unshift($alias_name, $v['goods_name']);
                        $alias = preg_replace("/\s+/",'',$v['alias_name']);
                        $list[$k]['id']         = M('goods')->where(['goods_name' =>  $alias])->getField('id');
                        $list[$k]['cate_id']    = M('goods')->where(['goods_name' =>  $alias])->getField('cate_id');
                        $list[$k]['cate_name']  = M('goods_category')->where(['id' => $list[$k]['cate_id']])->getField('title');
                        if(!$list[$k]['id']){
                            $list[$k]['id'] = '';
                        }  
                        $list[$k]['goods_name'] = $v['alias_name'];
                        $list[$k]['alias_name'] = implode(',',$alias_name); 
                    }else{
                        $alias = preg_replace("/\s+/",'',$v['goods_name']);
                        $list[$k]['id']         = M('goods')->where(['goods_name' =>  $alias])->getField('id');
                        $list[$k]['cate_id']    = M('goods')->where(['goods_name' =>  $alias])->getField('cate_id');
                        $list[$k]['cate_name']  = M('goods_category')->where(['id' => $list[$k]['cate_id']])->getField('title');
                        if(!$list[$k]['id']){
                            $list[$k]['id'] = '';
                        }  
                        $alias_name = M('search_hot')->where(['alias_name' => $v['goods_name']])->limit(2)->getField('goods_name',true);
                        $list[$k]['goods_name'] = $v['goods_name'];
                        if($alias_name){
                            $list[$k]['alias_name'] = implode(',',$alias_name);
                        }else{
                             $list[$k]['alias_name'] = '';
                        }
                         
                    }
                }
                $list = array_unique_fb($list);
                foreach ($list as $k => $v) {
                    $map[$k]['id']           = $v['id'];
                    $map[$k]['cate_id']      = $v['cate_id'];
                    $map[$k]['cate_name']    = $v['cate_name'];
                    $map[$k]['goods_py']   = preg_replace("/\s+/",'',$v['goods_py']);
                    $map[$k]['goods_name'] = preg_replace("/\s+/",'',$v['goods_name']);
                    $map[$k]['alias_name'] = preg_replace("/\s+/",'',$v['alias_name']);
                }
                //过滤 只有用户输入的是拼音的时候 才会 走下面的方法
                if($goods_py){
                    //将用户输入的拼音分割成数组
                    foreach($map as $k => $v){
                        $domain = strstr($v['goods_py'],$goods_py);
                        if(!$domain){
                            unset($map[$k]);
                        }
                    }
                }
                $map = array_values($map);
                $lists = array();
                foreach ($map as $k => $v) {
                    $lists[$k]['id']         = $v['id'];
                    $lists[$k]['cid']        = $v['cate_id'];
                    $lists[$k]['cn']         = $v['cate_name'];
                    $lists[$k]['goods_name'] = $v['goods_name'];
                    $lists[$k]['alias_name'] = $v['alias_name'];
                }
            }else{
                $lists = '';
            }
            if($lists){
                $this->ajaxDie(1,$lists);
            }else{
                $this->ajaxDie(0);
            }
    }
}
