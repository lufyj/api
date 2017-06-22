<?php

namespace Admin\Model;
use Think\Model;

/**
 * 分类模型
 * @author 吴鹏飞重构
 */
class GoodsCategoryModel extends Model{
	
	//提交时需要验证的数据
    protected $_validate = array(       
    	array('title', '', '分类名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '分类名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH)    	
    );
	
    //自动提交数据
    protected $_auto = array(        
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH)        
    );
    
    /**
     * 获取
     * @param string $field
     */
    public function getList($field = 'id,title',$order = 'length(title),sort'){
    	return $this->field($field)->where(array('status' => 1))->order($order)->select();
    }

    /**
     * 获取分类详细信息
     * @param unknown $id
     * @param string $field
     * @return 
     */
    public function info($id, $field = true){
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }   

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('gt', -1));
        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }
    
    /**
     * 更新商品分类信息
     * @return 
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }

        //更新分类缓存
        //S('sys_class_list', null);

        //记录行为
        //action_log('update_class', 'class', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }
}
