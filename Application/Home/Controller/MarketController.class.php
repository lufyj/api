<?php
namespace Home\Controller;

/**
* 前台价格行情控制器
* @author wpf
* @date 2016-12-6下午4:45:17
*/
class MarketController extends HomeController {	
	/* 价格详情列表 */	
	public function priceInfo(){
		$p  = I('get.p', 0, 'intval'); // 当前页码
		$id = I('get.id', 0, 'intval');//获取当前分类id		
		$limitRecord = 16; //单页最多几条数据		
		$condition = array();//查询提交
		$id > 0 && $condition['cate_id'] = $id;
		$searchParams = array('cure=true'); //搜索条件
		
		$model = D("GoodsPrice");
		$fields = 'id,goods_name,goods_attr_name,origin_area,price';
		
		$count = $model->field($fields)->where($condition)->count();
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
		$list = $model->field($fields)
			->where($condition)
			//->order('id desc')
			->limit($limit)
			->select();
		/**********大计算量开始********/
		$modelH = M('GoodsPriceHistory');		
		$clearData=array();
		$t1 = microtime(true);
		foreach ($list as $k => $v){
			$where = array('goods_price_id' => $v['id']);
			//计算昨日对比			
			$yesterdayPrice = $modelH->where($where)->order('id desc')->getField('price');
			$list[$k]['dayRate'] = number_format((((float)$v['price']/(float)$yesterdayPrice)-1)*100, 2);						
			//药品涨跌度对比(年，月，季度，周)
			$priceRange = $model->priceRange($v['id']);			
			$list[$k]['weekRate'] = $priceRange['week']*100;
			$list[$k]['monthRate'] = $priceRange['month']*100;
			$list[$k]['quarterRate'] = $priceRange['quarter']*100;
			$list[$k]['yearRate'] = $priceRange['year']*100;
			
			/******************计算列表里的走势（稳，少，畅，升，降）******************/
			/* $prices = $modelH->where($where)->getField('price', true);
			$minArg = $maxArg = array();			 
			//先排序，除去一个最大值，除去一个最小值
			sort($prices);
			$pcount = count($prices);
			unset($prices[0], $prices[$pcount-1]);
			$priceSum = array_sum($prices);
			$priceArg = (float)$priceSum/($pcount-2);
			foreach ($prices as $v){
				if((float)$v > $priceArg){
					$minArg[] = $v;
				}elseif((float)$v < $priceArg){
					$maxArg[] = $v;
				}
			}
			$minSum = array_sum($minArg);
			$maxSum = array_sum($maxArg);
			$clearData[]=$maxSum-$minSum; */			 
		}
		$t2 = microtime(true);
		//echo $t2-$t1;		
		//dump($clearData);
		//在这里需要排序，即将今日变化的数据显示在最前面				
		/******************/
		if($count > $limitRecord){
			//生成分页html
			$pageModel = new \Org\Com\Page;
			$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams));
			$this->assign('pageHtml', $pageHtml);
		}
				
		$cacheData = $this->getIndex_Cache();//先判断缓存中是否存在
		$cates = $cacheData['cates'];
		
		$this->assign('cates', $cates);
		$this->assign('list', $list);
		$this->meta_title = '价格列表';
		$this->display();
	}
	/* 历史价格信息 */
	public function priceHistory(){
		$params = I('get.');		
		
		$model = M('GoodsPrice');
		$list = $model->field('goods_attr_name,origin_area')
			->where(array('goods_name' => $params['q']))
			->select();		
		
		$attrs = $areas = array(); 
		foreach ($list as $k => $v){			
			if(!in_array($v['origin_area'], $areas)){
				$areas[] = $v['origin_area'];
			}
			if(!in_array($v['goods_attr_name'], $attrs) && $v['origin_area'] == $params['area']){
				$attrs[] = $v['goods_attr_name'];
			}
		}
		$this->assign('attrs', $attrs);
		$this->assign('areas', $areas);
		$this->meta_title = '价格历史信息';
		$this->display();
	}
	/* 获取价格历史数据|加载每年的月份数据ajaxGetHistoryMonth */
	public function ajaxGetHistoryPrice(){
		if(IS_AJAX && IS_GET){
			$params = I('get.');		
			$condition = array('goods_name' => $params['q'], 'origin_area' => $params['area'], 'goods_attr_name' => $params['attr']);
			$info = M('GoodsPrice')->field('id,price')->where($condition)->find();
			if($info){
				switch ($params['inx']){
					case 1:
						$monthList = M('GoodsPriceMonth')->field('price,in_month')
							->where(array('goods_price_id' => $info['id']))
							->select();
						$this->ajaxReturn(array('code' => 1, 'data' => $monthList));
						break;
					case 2:
						//查询时间段的涨跌幅
						$modelH = M('GoodsPriceHistory');
						$startDate = date('Ymd',strtotime($params['sDate']));
						$endDate = date('Ymd',strtotime($params['eDate']));;
						$startPrice = (float)$modelH->where(array('goods_price_id' => $info['id'],'create_time' => array('elt', (int)$startDate)))->order('create_time desc')->getField('price');						
						$endPrice = (float)$modelH->where(array('goods_price_id' => $info['id'],'create_time' => array('elt', (int)$endDate)))->order('create_time desc')->getField('price');
						if($startPrice > 0 && $endPrice > 0){
							$rate = number_format((((float)$endPrice/(float)$startPrice)-1)*100, 2);
						}else{
							$rate = 0;
						}						
						$this->ajaxReturn(array('code' => 1, 'data' => $rate));
						break;
					default:
						$req = array();
						$req['price'] = $info['price'];
						$historyList = M('GoodsPriceHistory')->field('price as value,create_time as date')
							->where(array('goods_price_id' => $info['id']))
							->order('create_time')
							->select();						
						if(count($historyList) > 0){
							foreach ($historyList as $k => $v){
								$historyList[$k]['date'] = date('Y-m-d', strtotime($v['date'])); 		
							}
							$req['data'] = $historyList;
							$req['startDate'] = $historyList[0]['date'];
							$req['endDate'] = end($historyList)['date'];
							$this->ajaxReturn(array('code' => 1, 'data' => $req));
						}	
						break;
				}															
			}
			$this->ajaxReturn(array('code' => 0, 'msg' => '暂无数据'));		
		}
	}
	/* 根据产地获取规格 */
	public function ajaxGetAttr(){
		if(IS_AJAX && IS_GET){
			$params = I('get.');
			$condition = array('goods_name' => $params['q'], 'origin_area' => $params['area']);
			$priceList = M('GoodsPrice')->where($condition)->getField('goods_attr_name', true);
			
			$req = array();
			foreach ($priceList as $k => $v){
				$req[] = $v;
			}
			$this->ajaxReturn(array('code' => 1, 'data' => $req));		
		}
	}	
}