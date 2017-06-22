<?php
// +----------------------------------------------------------------------
// | 版权：(c) 2017 http://www.yaoduwang.com All rights reserved.
// +----------------------------------------------------------------------
// | 作者: wpf <871927431@qq.com>
// +----------------------------------------------------------------------
// | 日期: 2017-03-01 10:40:46
// +----------------------------------------------------------------------
// | 描述: 前台药材模型
// +----------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class CYsdtModel extends  BaseModel{
	
	/* 获取行情信息 */
	public function getLists($type,$market,$minId,$keyword,$goods_name){
		$num         =  10;
		$minId       && $data['id']         = array('lt',$minId);
		$keyword     && $data['title']      = array('like',$keyword.'%');

		if($type == '1'){//天天行情

			if($market == '1'){//亳州
				$list = $this->_gethx('c_tthq_1',$data,$num);
			}else if($market == '2'){//安国
				$list = $this->_gethx('c_tthq_2',$data,$num);
			}else if($market == '3'){//玉林
				$list = $this->_gethx('c_tthq_3',$data,$num);
			}else if($market == '4'){//成都
				$list = $this->_gethx('c_tthq_4',$data,$num);
			}
		}else if($type == '2'){//产地行情
			$list = $this->_gethx('c_cdxx',$data,$num);
		}else if($type == '3'){//药市动态
			$list = $this->_gethx('c_ysdt',$data,$num);
		}

		foreach ($list as $k => $v) {
			$list[$k]['descriptions'] = strip_tags($v['description']);
		}
		return $list;
	}

	/* 获取行情信息 */
	public function getdetails($type,$market,$id){
		
		$id    &&   $data['id'] = $id;

		if($type == '1'){//天天行情

			if($market == '1'){//亳州
				$list = $this->_gethx('c_tthq_1',$data);
			}else if($market == '2'){//安国
				$list = $this->_gethx('c_tthq_2',$data);
			}else if($market == '3'){//玉林
				$list = $this->_gethx('c_tthq_3',$data);
			}else if($market == '4'){//成都
				$list = $this->_gethx('c_tthq_4',$data);
			}
		}else if($type == '2'){//产地行情
			$list = $this->_gethx('c_cdxx',$data);
		}else if($type == '3'){//药市动态
			$list = $this->_gethx('c_ysdt',$data);
		}

		$list['content']      = strip_tags($list['content']);
		return $list;
	}	

	//获取信息
	private function _gethx($table,$data,$num = 0){
		if($num){//判断是获取列表 还是获取详情
			$field = 'id,title,description,view,create_time';
			$list = M($table)->field($field)
			  ->where($data)
			  ->order('id desc')
			  ->limit($num)
			  ->select();
		}else{
			$field = 'id,title,view,create_time,author,content';
			$list = M($table)->field($field)
			  ->where($data)
			  ->find();

			//增加浏览次数
			M($table)->where(['id' => $data['id']])->setInc('view',1);
			//给详情加上上一篇  下一篇id
			$list['prev_id']         =  M($table)->where(['id' => array('gt',$data['id'])])->getField('id');
			$list['next_id']         =  M($table)->where(['id' => array('lt',$data['id'])])->order('id desc')->getField('id');
			$list['prev_title']      =  M($table)->where(['id' => array('gt',$data['id'])])->getField('title');
			$list['next_title']      =  M($table)->where(['id' => array('lt',$data['id'])])->order('id desc')->getField('title');
			$list['prev_id']         || $list['prev_id']     = '';
			$list['next_id']         || $list['next_id']     = '';
			$list['prev_title']      || $list['prev_title']  = '';
			$list['next_title']      || $list['next_title']  = '';
		}
		return $list;
	}

	/***1.4版本新增开始****/
	/**
	 * 返回行情精选列表信息
	 * 
	 */
	public function getMarketRecommend(){
		//1 药市动态 2品种分析 3 最新资讯  4 产地信息  5 天天行情
		$sort  = array(1,2,3,4,5);//后续由配置决定5个表中信息优先展示顺序
		
		foreach ($sort as $k => $v) {
			$type      = $this->_getTableType($v);//获取类型
			$tableName = $this->_getTableName($v);//获取表名

			$recommend[$k] = D("$tableName")->field('id,title,goods_id as gid, goods_name as gn,thumb as img,create_time as time')
					->where(['is_recommend' => 1])
					->order('id desc')
					->find();
			//如果表中没有推荐数据 则将该数组移除 有推荐数据则将时间和type文字加上
			if($recommend[$k]){
				$recommend[$k]['type'] = $type;
				$recommend[$k]['type_id'] = $v;
				$recommend[$k]['time'] = substr($recommend[$k]['time'],0,10);
			}else{
				unset($recommend[$k]);
			}
			

			//获取行情置顶信息
			$top[$k] = D("$tableName")->field('id,title,goods_id as gid, goods_name as gn,create_time as time')
					->where(['is_top' => 1])
					->order('id desc')
					->select();
			//如果表中没有置顶数据 则将该数组移除 有置顶数据则将时间和type文字加上
			foreach ($top[$k] as $k1 => $v1) {
				if($top[$k]){
					$top[$k][$k1]['type'] = $type;
					$top[$k][$k1]['time'] = substr($top[$k][$k1]['time'],0,10);
				}else{
					unset($top[$k]);
				}
			}			
		}

		//将置顶信息降维
		$newTop = array();
		foreach($top as $key=>$val){
    		foreach($val as $k=>$v){
		      $newTop[] = $v;
		    }
		}
		//将newTop按时间排序
		$sorts = array(  
	        'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
	        'field'     => 'time',       //排序字段  
		);

		$arrSort = array();//声明数组

		foreach($newTop as $k => $v){  
		    foreach($v as $key=>$val){  
		        $arrSort[$key][$k] = $val;  
		    }  
		}
		if($sorts['direction']){  
		    array_multisort($arrSort[$sorts['field']], constant($sorts['direction']), $newTop);  
		} 

		$data = array_merge($recommend,$newTop);//内容组合
		
		return $data;
	}
	/**
	*药市动态 + 品种分析 + 最新资讯 +产地列表信息集合  
	*1.4版本
	**/
	public function getMarketMuster(){
		$num        = 10;
		$type       = I('post.type',1,'intval');//1 药市动态  2 品种分析  3 最新资讯 4 产地信息
		$minId      = I('post.minId',0,'intval');
		$minId      &&  $data['id'] = array('lt',$minId);
		$tableName  = $this->_getTableName($type);
		$tableType  = $this->_getTableType($type);

		$data = D($tableName)->field('id,title,goods_id as gid, goods_name as gn,create_time as time')
			->where($data)
			->limit($num)
			->order('id desc')
			->select();
		if($data){
			foreach ($data as $k => $v) {
				$data[$k]['type'] = $tableType;
				$data[$k]['time'] = substr($v['time'],0,10);
			}
		}
		
		return $data;
	}
	/**
	*药市动态 + 品种分析 + 最新资讯 +产地信息+ 精选(天天行情)详情 
	*1.4版本
	**/
	public function getMarketDetails($id,$type){
	
		$data['id'] = $id;
		$tableName  = $this->_getTableName($type);

	   	//用户点击查看 给该行情阅读量加1
        M($tableName)->where($data)->setInc('view',1);

		$data = D($tableName)->field('id,title,description as des,thumb,author,content,create_time as time')
			->where($data)
			->find();
		if($data){
			$data['time']    = substr($data['time'],0,10);
			$data['content'] = preg_replace('/font\-size\:\w{1,2}.\w{1,5}\;/','',$data['content']);
		}

		return $data;
	}
	/* 获取天天行情列表信息  1.4版本*/
	public function getList($market,$minId,$keyword,$goods_name){
		$num         =  10;
		$minId       && $data['id']         = array('lt',$minId);
		$keyword     && $data['title']      = array('like',$keyword.'%');
		$goods_name  && $data['goods_name'] = $goods_name;

	
		if($market == '1'){//亳州
			$list = $this->_gethxs('c_tthq_1',$data,$num);
		}else if($market == '2'){//安国
			$list = $this->_gethxs('c_tthq_2',$data,$num);
		}else if($market == '3'){//玉林
			$list = $this->_gethxs('c_tthq_3',$data,$num);
		}else if($market == '4'){//成都
			$list = $this->_gethxs('c_tthq_4',$data,$num);
		}

		return $list;
	}

	/* 获取天天行情详情信息   1.4版本 */
	public function getdetail($market,$id){
		
		$id    &&   $data['id'] = $id;


		if($market == '1'){//亳州
			$list = $this->_gethxs('c_tthq_1',$data);
		}else if($market == '2'){//安国
			$list = $this->_gethxs('c_tthq_2',$data);
		}else if($market == '3'){//玉林
			$list = $this->_gethxs('c_tthq_3',$data);
		}else if($market == '4'){//成都
			$list = $this->_gethxs('c_tthq_4',$data);
		}

		$list['content']      = strip_tags($list['content']);
		return $list;
	}	

	//获取行情信息 hx(行情信息缩写) 1.4版本
	private function _gethxs($table,$data,$num = 0){
		if($num){//判断是获取列表 还是获取详情
			$field = 'id,title,goods_name as gn,create_time as time';
			$list = M($table)->field($field)
			  ->where($data)
			  ->order('id desc')
			  ->limit($num)
			  ->select();
			foreach ($list as $k => $v) {
				$list[$k]['type'] = '天天行情';
				$list[$k]['time'] = substr($v['time'],0,10);
			}
		}else{
			$field = 'id,title,view,create_time as time,thumb,goods_name as gn,author,content';
			$list = M($table)->field($field)
			  ->where($data)
			  ->find();
			//增加浏览次数
			M($table)->where(['id' => $data['id']])->setInc('view',1);
			//给详情加上上一篇  下一篇id
			$list['prev_id']         =  M($table)->where(['id' => array('gt',$data['id'])])->getField('id');
			$list['next_id']         =  M($table)->where(['id' => array('lt',$data['id'])])->order('id desc')->getField('id');
			$list['prev_title']      =  M($table)->where(['id' => array('gt',$data['id'])])->getField('title');
			$list['next_title']      =  M($table)->where(['id' => array('lt',$data['id'])])->order('id desc')->getField('title');
			$list['prev_id']         || $list['prev_id']     = '';
			$list['next_id']         || $list['next_id']     = '';
			$list['prev_title']      || $list['prev_title']  = '';
			$list['next_title']      || $list['next_title']  = '';
		}
		return $list;
	}


	//获取表名  1.4版本
    private function _getTableName($type){

        switch($type){
            case 1:
                $tableName ='c_ysdt';
                break;
            case 2:
                $tableName ='c_pzfx';
                break;
            case 3:
                $tableName ='c_zxzx';
                break;
            case 4:
                $tableName ='c_cdxx';
                break;
            case 5:
            	$tableName ='c_tthq_1';
        }
        return $tableName;    
    }
    //获取表含义 1.4版本
    private function _getTableType($type){

        switch($type){
            case 1:
                $tableType ='药市动态';
                break;
            case 2:
                $tableType ='品种分析';
                break;
            case 3:
                $tableType ='最新资讯';
                break;
            case 4:
                $tableType ='产地信息';
                break;
            case 5:
        		$tableType ='天天行情';
        }
        return $tableType;    
    }
    /***1.4版本新增结束***/
}
?>