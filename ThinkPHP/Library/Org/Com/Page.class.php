<?php
/*
 * 亿建联 前台分页样式模块
 * 用于前台分页样式
 *
 * @package		model
 * @author		wangyouworld
 * @copyright	Copyright (c) 2016, ejianlian
 * @link		http://ejianlian.com/
 * @since		Version 1.0
 */

namespace Org\Com;

class Page {

	private $url = ''; // 搜索 URL
	private $page_start_tag = ''; // 分页开始标签
	private $page_end_tag = ''; // 分页结束标签

	private $now_tag = ''; // GET 参数 当前页标签
	private $perpage_tag = ''; // GET 参数 分页标签

	private $page_parent_total_start_tag = ''; // 设置上级总页数 开始标签
	private $page_parent_total_end_tag = ''; // 设置上级总页数 结束标签

	private $page_total_start_tag = ''; // 总页数开始标签
	private $page_total_end_tag = ''; // 总页数结束标签
 
	private $page_now_start_tag = ''; // 设置当前页 开始标签
	private $page_now_end_tag = ''; // 设置当前页 结束标签

	private $other_page_tag = ''; // 其他分页标签

	private $show_page = 0; // 设置左右显示分页数
	private $go_to_page = ''; // 跳转 HTML 内容

	public $page_prev_tag = 'javascript:;'; // 上一页
	public $page_next_tag = 'javascript:;'; // 下一页

	private $total = 0; // 总记录数
	private $now_page = 1; // 当前页
	private $page_num = 1; // 分页数
	
	private $jump = TRUE;//是否显示跳转按钮

	public function show($total, $perpage = 1, $now_page = 1, $url='', $jump = TRUE , $show_page = 4) {
		$this->total = $total;
		$this->page_num = ceil($total / $perpage); // 总页数

		// 分页范围处理
		$now_page = abs(intval($now_page));
		$now_page = ($now_page == 0) ? 1 : $now_page;
		$now_page = ($now_page > $this->page_num) ? $this->page_num : $now_page;

		$this->now_page = $now_page; // 设置当前页
		$this->setPageStyle('div', 'page'); // 分页总样式
		$this->setParentTotalStyle('span', 'count', '共有', '条记录'); // 外部总页数样式
		$this->setTotalStyle('i', '', $total, '', ''); // 总页数样式
		$this->setNowPageStype('a', 'page_click current', $now_page); // 当前页样式
		$this->setPageLink(); // 设置分页链接
		$this->setPageGetVal('p', 'perpage'); // 设置 GET 参数标签 用于控制分页显示
		if (empty($url)) $url = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$this->setPageLink($url); // 设置 未加分页参数 的URL
		$this->jump = $jump;
		$this->show_page = $show_page; // 设置左右显示分页数

		
		$this->setOtherPage('a', 'page_click'); 
		$this->setGoToPage(); 
		$html = '';
		$html .= $this->page_start_tag; // 分页开始标签
		$html .= $this->page_parent_total_start_tag.$this->page_total_start_tag;
		$html .= $this->page_total_end_tag.$this->page_parent_total_end_tag;
		$html .= $this->other_page_tag; // 其他分页标签
		$html .= $this->go_to_page; // 跳转标签
		$html .= $this->page_end_tag; // 分页结束标签
		
		return $html;
	
	}
	public function show2($total, $perpage = 1, $now_page = 1){
		$pager['count']  = $total;
		$pager['pagecnt'] = ceil($total / $perpage);
		$pager['showstart'] = ($now_page <= 3) ? 1: (int)($now_page-3);
		$pager['showend']   = ($now_page >= $pager['pagecnt'] - 2)?(int)$pager['pagecnt']:(int)($now_page + 3);
		$pager['current'] = min($now_page, $pager['pagecnt']);
		$pager['next']	  = min($now_page+1, $pager['pagecnt']);
		$pager['prev']	  = max(1, $now_page-1);
		$pager['showitem']= '';
		 
		if($pager['showstart'] > 1) { $pager['showitem'] .= '<span>...</span>&nbsp;&nbsp;'; }
		for($i = $pager['showstart']; $i <= $pager['showend']; $i++){
			$class = ($i == $pager['current']) ? 'current' : '';
			$pager['showitem'] .= "<a href=\"javascript:turnpage({$i})\" class='{$class}'>{$i}</a>";
		}
		if($pager['pagecnt'] > $pager['showend']) $pager['showitem'] .= '<span>...</span>';
		return $pager;
	}

	/*
	 * 设置 GET 参数分页标签
	 * @param string $now_tag 当前页标签 如 p
	 * @param string $perpage_tag 分页标签 如 perpage
	 *
	 * @return boolean
	 */
	public function setPageGetVal($now_tag='', $perpage_tag='') {
		$this->now_tag = $now_tag;
		$this->perpage_tag = $perpage_tag;
		return TRUE;
	}

	/* 
	 * 设置分页样式
	 *
	 * 示例 ('div', 'page')
	 * 效果 <div class='page'></div>
	 *
	 * @param string $tag 分页 html 标签
	 * @param string $class 分页样式
	 *
	 * @return boolean
	 */
	public function setPageStyle($tag, $class='') {
		$class = !empty($class) ? "class='{$class}'" : '';
		$this->page_start_tag = "<{$tag} {$class}>";
		$this->page_end_tag = "</{$tag}>";
		return TRUE;
	}

	/* 
	 * 设置上级总页数样式
	 *
	 * 示例 ('span', 'count', '共有', '条记录')
	 * 效果 <span class='count'>共有条记录</span>
	 *
	 * @param string $tag 分页 html 标签
	 * @param string $class 分页样式
	 * @param int $val 总页数
	 * @param string $cn1 前置描述符 如 共有
	 * @param string $cn2 后置描述符 如 条记录
	 *
	 * @return boolean
	 */
	public function setParentTotalStyle($tag, $class='', $cn1='', $cn2='') {
		$class = !empty($class) ? "class='{$class}'" : '';
		$this->page_parent_total_start_tag = "<{$tag} {$class}>{$cn1}";
		$this->page_parent_total_end_tag = "{$cn2}</{$tag}>";
		return TRUE;
	}

	/* 
	 * 设置总页数样式
	 * 示例 ('i', '', 100, '', '')
	 * 效果 <i>100</i> 如果结合 setParentTotalStyle 则 <span class='count'>共有<i>100</i条记录</span>
	 *
	 * @param string $tag 分页 html 标签
	 * @param string $class 分页样式
	 * @param int $val 总页数
	 * @param string $cn1 前置描述符 如 共有
	 * @param string $cn2 后置描述符 如 条记录
	 *
	 * @return boolean
	 */
	public function setTotalStyle($tag, $class='', $val=0, $cn1='', $cn2='') {
		$class = !empty($class) ? "class='{$class}'" : '';
		$this->page_total_start_tag = "<{$tag} {$class}>{$cn1} {$val}";
		$this->page_total_end_tag = "{$cn2}</{$tag}>";
		return TRUE;
	}

	/*
	 * 设置当前页样式
	 * @param string $tag 分页 html 标签
	 * @param string $class 分页样式
	 * @param int $val 值
	 */
	public function setNowPageStype($tag, $class='', $val=0) {
		$class = !empty($class) ? "class='{$class}'" : '';
		$this->page_now_start_tag = "<{$tag} {$class}>{$val}";
		$this->page_now_end_tag = "</{$tag}>";
		return TRUE;
	}

	/*
	 * 设置分页链接
	 *
	 * @param string $url 待处理的链接
	 * @return string 未加分页参数 的链接
	 */
	public function setPageLink($url='') {
		$this->url = $url;
	}

	/* 设置其他分页信息 */
	public function setOtherPage($tag, $class='') {
		// $this->show_page; // 左右显示数量
		$html = '';
		$class = !empty($class) ? "class='{$class}'" : '';
		$start_html = '';
		$end_html = "</{$tag}>";

		$i = ($this->now_page - $this->show_page);
		$i = ($i < 1) ? 1 : $i;
		$end = $this->now_page+$this->show_page;
		$end = ($end > $this->page_num) ? $this->page_num : $end;
		for (; $i<=$end; $i++) {
			// 如果为当前页
			if ($i == $this->now_page) {
				$html .= $this->page_now_start_tag.$this->page_now_end_tag;
			} else {
				$start_html = "<{$tag} {$class} href='{$this->url}&{$this->now_tag}={$i}'>{$i}";
				$end_html = "</{$tag}>";
				$html .= $start_html.$end_html;
			}
		}		
		// 上一页（2016年10月12日 吴鹏飞修改）
		if($this->now_page - 1 == 0){
			$start_html = "<{$tag} {$class} href='javascript:;'>上一页";			
		}else{
			$start_html = "<{$tag} {$class} href='{$this->url}&{$this->now_tag}=".($this->now_page-1)."'>上一页";
			//2017年2月9日 吴鹏飞修改
			$this->page_prev_tag = "{$this->url}&{$this->now_tag}=".($this->now_page-1);
		}		
		$end_html = "</{$tag}>";
		$html = $start_html.$end_html.$html;

		// 下一页（2016年10月12日 吴鹏飞修改）
		if($this->now_page == $this->page_num){			
			$start_html = "<{$tag} {$class} href='javascript:;'>下一页";
		}else{
			$start_html = "<{$tag} {$class} href='{$this->url}&{$this->now_tag}=".($this->now_page+1)."'>下一页";
			//2017年2月9日 吴鹏飞修改
			$this->page_next_tag = "{$this->url}&{$this->now_tag}=".($this->now_page+1);
		}		
		$end_html = "</{$tag}>";
		$html .= $start_html.$end_html;

		$this->other_page_tag = $html;
		return TRUE;
	}

	/* 设置跳转功能 */
	public function setGoToPage() {
		$this->go_to_page = '<script type="text/javascript">';
		$this->go_to_page .= 'function toSearchPage() {';
		$this->go_to_page .= 'var page=document.getElementById("myDefinePage").value;';
		$this->go_to_page .= 'var url = document.getElementById("myDefineUrl").value;';
		$this->go_to_page .= 'var tag = document.getElementById("myDefinePageTag").value;';
		$this->go_to_page .= 'if (page == "" || isNaN(page)) {';
		$this->go_to_page .= '	alert("输入有效跳转页数");';
		$this->go_to_page .= '	return false;';
		$this->go_to_page .= '}';
		$this->go_to_page .= 'location.href = url+"&"+tag+"="+page';
		$this->go_to_page .= '}';
		$this->go_to_page .='</script>';
		$this->go_to_page .= '<input type="hidden" id="myDefineUrl" value="'.$this->url.'" />';
		$this->go_to_page .= '<input type="hidden" id="myDefinePageTag" value="'.$this->now_tag.'" />';
		if($this->jump){
			$this->go_to_page .='<span class="num">跳转到第<input type="text" id="myDefinePage" class="numinput">页<a onclick="toSearchPage()" href="javascript:void(0);" class="sure">确定</a></span>';
		}		
	}

}
