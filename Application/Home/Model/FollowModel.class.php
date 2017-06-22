<?php 
namespace Home\Model;
use Think\Model;

/**
 * 关注 模型
 * @author wpf
 */
class FollowModel extends Model{	

	/**
	 * 对模型中用到的 wheresql 统一处理
	 * 
	 * @param string || array $wheresql SQL 语句
	 * @return string || array 返回加好的默认SQL条件
	 */
	public function toWheresql($wheresql = '') {
		$uid = session('user_sign.id');
		// 控制删除标记
		if (is_array($wheresql)){ $wheresql['uid'] = $uid; }
		if (is_string($wheresql)){ $wheresql .= empty($wheresql) ? 'uid='.$uid : ' AND uid='.$uid; }
	
		// 至少保证存在 where 条件
		$wheresql = empty($wheresql) ? ' 1 = 1 ' : $wheresql;
		return $wheresql;
	}
	
	/**
	 * 统计符合条件记录数
	 * 
	 * @param string $wheresql
	 * @return int 返回符合条件记录数
	 */
	public function countList($wheresql = '') {
		$wheresql = $this->toWheresql($wheresql);
		return $this->where($wheresql)->count();
	}	
	/**
	 * 判断该用户是否关注过该药品
	 * @param unknown $id
	 */
	public function isExist($gid){		
		
		$condition = $this->toWheresql(array( 'goods_id' => $gid ));
		return $this->field('id')->where($condition)->find();
	}
	/**
	 * 获取列表
	 * 
	 * @param unknown $condition
	 * @param number $offset 当前偏移量
	 * @param number $perpage 当前显示的数量
	 * @param string $orderby 排序
	 * @return array 返回所有结果
	 */
	public function getList($wheresql = '', $page = 0, $perpage = 10, $orderby = '') {
		$perpage = empty($perpage) ? 10 : abs(intval($perpage));
		$orderby = empty($orderby) ? 'a.create_time desc' : $orderby;

		$wheresql = $this->toWheresql($wheresql);		
		$this->alias('a')->field('a.goods_id,a.goods_name,a.uid,b.goods_img')
			->join('left join ydw_goods b on a.goods_id = b.id')
			->where($wheresql)
			->order($orderby);
	
		if ($page >= 0 ) $this->limit($page.','.$perpage);
		$list = $this->select();
	
		return $list;
	}	
	/**
	 * 添加一个关注
	 * @param unknown $gid  药品id
	 * @param string $gname 药品名称
	 * @return 
	 */
	public function setFollow($gid, $gname = ''){
		
		//判断商品是否存在
		$ginfo = D('Goods')->getOne($gid);
		if(!$ginfo){
			return array( 'code' => 0,	'msg'  => '该商品不存在');
		}
		//判断该用户是否关注过
		$exist = $this->isExist($gid);
		if($exist){
			return array( 'code' => 0,	'msg'  => '您已经关注过该药品');
		}		
		//组合数据
		$clean_data = array(
			'goods_id'	 => $gid,
			'goods_name' => $ginfo['goods_name'],		
			'uid'		 => session('user_sign.id'),
			'create_time'=> time()
		);		
		//插入数据并判断
		$res = $this->data($clean_data)->add();
		if($res !== false){
			return array( 'code' => 1,	'msg'  => '关注成功');
		}else{
			return array( 'code' => 0,	'msg'  => '关注失败');
		}
	}
	/**
	 * 取消一个关注
	 * @param unknown $gid
	 */
	public function cacelFollow($gid){
		
		$condition = $this->toWheresql(array( 'goods_id' => $gid ));
		return $this->where($condition)->delete();
		
	}
}
?>