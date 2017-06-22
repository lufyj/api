<?php
// +----------------------------------------------------------------------
// | Name:Excel
// +----------------------------------------------------------------------
// | Desc: Excel
// +----------------------------------------------------------------------
// | Date: 2015-04-01
// +----------------------------------------------------------------------
// | Author: un
// +----------------------------------------------------------------------
namespace Org\Com;
class Excel
{
    public $objPHPExcel = NULL; // 存储Excel类
    public $objWriter = NULL;
    public $maxRows = 65535; // 最大行
    public $maxCols = 255; // 最大列

    public function init() {

    	require_once THINK_PATH.'Library/Vendor/PHPexcel/PHPExcel.php';
    	require_once THINK_PATH.'Library/Vendor/PHPexcel/PHPExcel/IOFactory.php';
    	require_once THINK_PATH.'Library/Vendor/PHPexcel/PHPExcel/Writer/Excel2007.php';
    	require_once THINK_PATH.'Library/Vendor/PHPexcel/PHPExcel/Writer/Excel5.php';

    	$this->objPHPExcel = new \PHPExcel();

    	/*
    	$head = ARRAY('name' => '姓别', 'age' => '年龄');
    	$datas = ARRAY(0 => ARRAY( 'name' => '张三', 'age' => 18), 1=> ARRAY('name' => '李四', 'age' => 20));
    	$this->output('测试Excel',$head, $datas);
    	 */
    }

    /*
     * 提供数字，返回对应列号
     * 比较笨的方式，采用循环得出
     */
    private function getRowsNum($num) {
    	$loop = 0;
    	$charnum = 65;

    	for(; $loop < $num; $loop++) {
    		$quotient = intval($loop / 26);
    		$remainder = $loop % 26;
    		$f = $quotient>0? chr($charnum+$quotient-1) : '';
    		$s = $remainder>=0? chr($charnum+$remainder) : '';
    	}
    	return $f . $s;
    }
    /*
     * 将2维数据转换为1维
     * 留意 数组中需要存在 key name，export项。
     * 另外，2维数组需要包含序号为0的数组。
     */
    public function to1Arr($head) {
    	$newHead = ARRAY();
    	if (isset($head[0]) && is_array($head[0])) {
    		foreach ($head as $val) {
    			if ($val['export']) $newHead[$val['key']] = $val['name'];
    		}
    	} else {
    		$newHead = $head;
    	}
    	return $newHead;
    }

    /*
     * 将数据导出为excel表格
     *
     * $head 数据格式为键值形式，分别对应数据的键名和表头
     * $datas 数据
     *
     * 示例 ：
     * $head = ARRAY('name' => '姓别', 'age' => '年龄');
     * 则数据形式为 ：
     * $datas = ARRAY(0 => ARRAY( 'name' => '张三', 'age' => 18), 1=> ARRAY('name' => '李四', 'age' => 20));
     为标题信息 $data['list'] 为数据信息
    	 * $type 为 salary 时，将执行工资合计显示
     */
    public function output($filename, $head, $datas, $sheet='', $type='other') {
    	$head = $this->to1Arr($head); // 数据转换（2维转1维），如果存在的话
    	$colNum = count($datas); // 行数据
    	$rowNum = count($head); // 列数据
    	$colNum = ($colNum>$this->maxCols) ? $this->maxCols : $colNum; //  最大行
    	$rowNum = ($colNum>$this->maxCols) ? $this->maxRows : $rowNum; // 最大列
    	$sheet = empty($sheet) ? $filename : $sheet;

    	$this->objPHPExcel
    		->getProperties()  //获得文件属性对象，给下文提供设置资源
    		->setCreator( "亿建联")                 //设置文件的创建者
    		->setLastModifiedBy( "亿建联")          //设置最后修改者
    		->setTitle( $filename )    //设置标题
    		->setSubject( $filename )  //设置主题
    		->setDescription( $filename) //设置备注
    		->setKeywords( $filename)        //设置标记
    		->setCategory( $filename);                //设置类别

    	$rowStr = ''; // 列序号
    	$valStr = ''; // 临时存储值
    	$this->objPHPExcel->setActiveSheetIndex(0); // 设置活动的工作薄
    	$this->objPHPExcel->getActiveSheet()->setTitle($sheet);// 设置工作薄名称
    	$this->objPHPExcel->getActiveSheet()->setCellValue('A1', $sheet); // 设置A1单元格内容（即大表头）
    	$this->objPHPExcel->getActiveSheet()->mergeCells('A1:'.$this->getRowsNum($rowNum).'1'); // 合并单元格
    	// 对齐处理（没生效，原因不详）
    	// $this->objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    	$i_h = 0;
    	// $tmpInfo = '';

    	// 数组初始化
    	if ($type == 'salary') {
    		$countArr = ARRAY();
    		$ACD230_SUM = empty($datas) ? 0 : count($datas);
    		$countArr['AAC003'] = '共计 '. $ACD230_SUM. ' 人'; // 人数统计
    		// $countArr['AAC003'] = iconvArr($countArr['AAC003'] , 'utf-8', 'gbk');
    		$countArr['ACD23B'] = '总合计';
    	}

    	foreach ($head as $key_h => $val_h) {
    		$rowStr = $this->getRowsNum($i_h+1).'2'; // 设置表头位置
    		$this->objPHPExcel->getActiveSheet()->setCellValue($rowStr, $val_h); // 设置表头值
    		$i_d = 0;
    		// $tmpInfo .= $key_h.PHP_EOL;

    		// TRIM 是个坑，trim($key_h, 'CATE_') 你以为是多少？
    		if (substr($key_h, 0, 5) == 'CATE_') {
    			$countArr[$key_h] = 0;
    		}
    		foreach ($datas as $key_d => $val_d) {
    			$rowStr = $this->getRowsNum($i_h+1).($i_d+3);
    			$valStr = isset($val_d[$key_h]) ? $val_d[$key_h] : '';

    			// $tmpInfo .= $valStr.' || '.
    			// Excel 无法处理数字，仅对 CARD_NUMBER字段前加 ' 号以确定为字符型
    			// $valStr = (is_numeric($valStr) && $key_h == 'CARD_NUMBER') ? "'".$valStr : $valStr;
    			if ($key_h == 'CARD_NUMBER') {
    				$this->objPHPExcel->getActiveSheet()->setCellValueExplicit($rowStr, $valStr,PHPExcel_Cell_DataType::TYPE_STRING);
    			} else {
    				$this->objPHPExcel->getActiveSheet()->setCellValue($rowStr, $valStr);
    			}

    			if ($type == 'salary') {
    				if (substr($key_h,0, 5) == 'CATE_') {
    					$countArr[$key_h] = isset($countArr[$key_h]) ? $countArr[$key_h] + $valStr : 0;
    				}
    			}

    			$i_d++;
    			if ($i_d > $colNum) break;
    		}
    		// $tmpInfo .= PHP_EOL;
    		$i_h++;
    		if ($i_h > $rowNum) break;
    	}

    	/* 合计项写入表格 start*/
    	if ($type == 'salary') {
    		$i_d = 0;
    		foreach ($head as $key_h => $val_h) {

    			$rowStr = $this->getRowsNum($i_d+1).($ACD230_SUM+4);
    			$valStr = isset($countArr[$key_h]) ? $countArr[$key_h] : '';
    			// echo " {$rowStr} , {$key_h} , {$valStr}<br>";
    			$this->objPHPExcel->getActiveSheet()->setCellValue($rowStr, $valStr);

    			$i_d +=1;
    		}
    	}

    	$this->objWriter = new \PHPExcel_Writer_Excel2007($this->objPHPExcel );
    	ob_end_clean();  //清空缓存
    	header("Pragma: public");
    	header("Expires: 0");
    	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    	header("Content-Type:application/force-download");
    	header("Content-Type:application/vnd.ms-execl");
    	header("Content-Type:application/octet-stream");
    	header("Content-Type:application/download");
    	header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
    	header("Content-Transfer-Encoding:binary");
    	$this->objWriter->save("php://output");
    	exit;
    }


    /*
     * 读取表格内容
     * 支持格式 txt xls
     * 如果是 txt 格式 将这按行 以空格分隔来处理数据项
     */
    public function input($head, $filename) {
    	$data = ARRAY();
    	$countCell = count($head);
    	$objReader = new \PHPExcel_Reader_Excel2007();

    	if(!$objReader->canRead($filename)){
    		$objReader = new \PHPExcel_Reader_Excel5();
    		if (!$objReader->canRead($filename)) {
    			return FALSE;
    		}
    	}

    	$objPHPExcel = $objReader->load($filename); //$filename可以是上传的文件，或者是指定的文件

    	$sheet = $objPHPExcel->getSheet(0);
    	$highestRow = $sheet->getHighestRow(); // 取得总行数
    	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
    	$k = 0;

    	//循环读取excel文件,读取一条,插入一条
    	//j表示从哪一行开始读取
    	//$a表示列号
    	for($j=1;$j<=$highestRow;$j++)
    	{
    		$c = 1;
    		foreach ($head as $key => $val) {
    			$data[$k][$key] = $objPHPExcel->getActiveSheet()->getCell($this->getRowsNum($c).$j)->getValue();
    			$c += 1;
    		}
    		$k += 1;

    	}

    	return $data;
    }

}
