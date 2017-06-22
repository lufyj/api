<?php
namespace Admin\Model;
use Think\Model;
/**
* 价格药材模型
* @author wpf
* @date 2016-11-30下午12:35:16
*/
class GoodsPriceModel extends Model {
	/* 获取价格药材列表 */
	public function getList($params, $show_num = 0, $page_num = 0){
		$query = '1=1';
		$this->_search($params, $query);//组合查询条件
	
		$count = $this->where($query)->count();//获取总条数
		$total_page = ceil($count / $show_num);//获取总页数
		if($total_page > 0 && $total_page < $page_num){
			$page_num = $total_page;
		}elseif ($total_page == 1){
			$total_page = 1;
		}
		$limit = ($page_num - 1) * $show_num . ',' .$show_num;
		$list = $this->where($query)
			->order('id desc')
			->limit($limit)
			->select();
		return array(
			'list'	 => $list,
			'page_num' => $page_num,
			'show_num' => $show_num,
			'all_count'  => $count,
			'total_page' => $total_page
		);
	}
	/* 根据参数生成查询字符串 */
	private function _search($params, &$query){
		foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'cate_id':
    				if((int)$value > 0){
    					$query .= ' and cate_id ='.$value;
    				}
    				break;
    			case 'goods_name':
    				if($value){
    					$query .= ' and goods_name like \'%'. $value .'%\'';
    				}
    				break;
    		}
    	}
	}
	/* 处理提交的数据 */
	public function operateData($data){
		//首先根据药材，规格，产地来确定是否已经有类似数据（无论编辑还是添加都需要此条件）
		$condition = array(
			'goods_id' => $data['goods_id'],
			'origin_area' => $data['origin_area'],
			'goods_attr_id' => $data['goods_attr_id']
		);
		$today = date('Y-m-d');
		$todayDate = date('Ymd');
		$yesterdayDate = $todayDate-1;
		$select_date = $data['select_date'];
		$insertDate = date('Ymd', strtotime($select_date));
		$exist = $this->field('id,price,create_time')->where($condition)->find();
		$modelH   = M('GoodsPriceHistory');
		if($exist){			
			//判断是否是当天的日期（如果没有日期就是当天的）
			if(($select_date && $select_date == $today) || !$select_date){				
				//如果为当天日期，则先将goods_price表中的price与goods_price_history中的price进行比较，如果一样则更新日期，如果不一样则插入新的数据，
				//日期都为当前日期的前一天			
				$lastHistory = $modelH->where(array('goods_price_id' => $exist['id']))->order('create_time desc')->find();
				if((float)$lastHistory['price'] != (float)$exist['price']){					 
					$lastPrice = $modelH->where(array('goods_price_id' => $exist['id'],'create_time' => array('lt', $yesterdayDate)))->order('create_time')->getField('price');
					if((float)$lastPrice != (float)$exist['price']){
						if($yesterdayDate == $lastHistory['create_time']){
							$effectRow = $modelH->where(array('id' => $lastHistory['id']))->setField('price', $exist['price']);
							if($effectRow !== false){
								$this->error = '操作成功';
							}
						}else{
							$insertHid = $modelH->data(array('price' => $data['price'],'create_time' => $yesterdayDate))->add();
							if((int)$insertHid > 0){
								$this->error = '操作成功';
							}
						}
					}else{
						$this->error = '操作成功';
					}							
				}else{
					$this->error = '操作成功';
				}
				if($this->error = '操作成功'){
					$effectRow = $this->where(array('id' => $exist['id']))->setField(array('price' => $data['price'],'create_time' => $todayDate));
					if($effectRow !== false) return 1;
				}
			}else{
				//如果选择日期存在，且不是当天日期，就根据goods_price_id和create_time去goods_price_history表中查看是否有该条数据。如果有就更新其值，如果
				//没有就插入其值。在插入或更新时要判断该日期的上一个值与下一个值与当前要插入的值是否相等，相等任何一个即不处理；否则就插入				
				$lastPrice = $modelH->where(array('goods_price_id' => $exist['id'],'create_time' => array('lt', $insertDate)))->order('create_time')->getField('price');
				if((float)$data['price'] != (float)$lastPrice){
					$nextPrice = $modelH->where(array('goods_price_id' => $exist['id'],'create_time' => array('gt', $insertDate)))->order('create_time')->getField('price');
					if((float)$data['price'] != (float)$nextPrice){
						//插入新日期数据
						$insertHistory = $modelH->where(array('goods_price_id' => $exist['id'],'create_time' => $insertDate))->find();
						if($insertHistory){
							$effectRow = $modelH->where(array('id' => $insertHistory['id']))->setField('price', $data['price']);
							if($effectRow !== false){
								$this->error = '操作成功';return 1;
							}
						}else{
							$insertHid = $modelH->data(array('goods_price_id' => $exist['id'],'price' => $data['price'],'create_time' => $insertDate))->add();
							if((int)$insertHid > 0){
								$this->error = '操作成功';return 1;
							}
						}
					}else{
						$this->error = '无效操作';return 1;
					}
				}else{
					$this->error = '无效操作';return 1;
				}
			}	
				
		}else{
			$historyData = array();	//用于保存历史记录数据
			//没有，先将数据保存到ydw_goods_price表中
			$data['create_time'] = $todayDate;
			$insertId = $this->data($data)->add();
			if((int)$insertId > 0){
				//插入成功则继续将药材价格保存到ydw_goods_price_history表中
				$historyData['price'] = $data['price'];
				$historyData['goods_price_id'] = $insertId;
				if(($select_date && $select_date == $today) || !$select_date){
					$historyData['create_time'] = $yesterdayDate;
				}else{
					$historyData['create_time'] = $insertDate;
				}				
				$insertHid = $modelH->data($historyData)->add();
				if((int)$insertHid > 0){
					$this->error = '操作成功';return 1;
				}
			}
		}
		$this->error = '操作失败';return 0;
		/* if($exist){
			//首先更新ydw_goods_price表中的price字段
			$res = $this->where(array('id' => $exist['id']))->setField('price', $data['price']);
			//如果存在药材-规格-产地一致的数据，则根据日期进行判定，是插入还是覆盖
			$modelH   = M('GoodsPriceHistory');
			if($select_date){
				//假如选择日期存在					
				$start_time = strtotime($select_date);
				$end_time = strtotime("$select_date +1 day");
			}else{
				//假如不存在，再去ydw_goods_price_history表中判断是否更新数据,同一天则覆盖，否则插入					
				$end_time = strtotime(date('Y-m-d',strtotime('+1 day')));										
			}
			$sql  = 'select id from ydw_goods_price_history where goods_price_id ='. $exist['id'] .' and create_time >='. $start_time .' and create_time <'. $end_time;
			$resH = $modelH->query($sql);
			if($resH && count($resH) > 0){
				//假如ydw_goods_price_history中存在数据，就更新价格
				$updateHid = $modelH->where(array('id' => $resH[0]['id']))->setField('price', $data['price']);
				if($updateHid !== false){
					$this->error = '更新成功';return 1;
				}
			}else{
				//不存在则重新插入数据
				$historyData['price'] = $data['price'];
				$historyData['create_time'] = $start_time;
				$historyData['goods_price_id'] = $exist['id'];
				$insertHid = $modelH->data($historyData)->add();
				if((int)$insertHid > 0){
					$this->error = '添加成功';return 1;
				}
			}
		}else{
			//没有，先将数据保存到ydw_goods_price表中
			$data['create_time'] = time();
			$insertId = $this->data($data)->add();
			if((int)$insertId > 0){
				//插入成功则继续将药材价格保存到ydw_goods_price_history表中
				$historyData['price'] = $data['price'];
				$historyData['goods_price_id'] = $insertId;
				if($select_date){
					$historyData['create_time'] = strtotime($data['select_date']);
				}else{
					$historyData['create_time'] = $start_time;
				}
				$insertHid = M('GoodsPriceHistory')->data($historyData)->add();
				if((int)$insertHid > 0){
					$this->error = '添加成功';return 1;
				}					
			}				
		}
		$this->error = '添加失败';return 0;		 */
	}
}