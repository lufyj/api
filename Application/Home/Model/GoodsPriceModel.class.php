<?php
/**
 * 抓取数据模型
 * User: jingwei
 * Date: 2016-11-30
 */
namespace Home\Model;

use Think\Model;

class GoodsPriceModel extends Model{

    //计算该年有多少周
    public function getTotalWeek($year){
        $weekDay=date('N',strtotime($year.'-12-31'));
        $day=($weekDay<=3)?(31-$weekDay):31;
        $weekNum=date('W',strtotime($year.'-12-'.$day));
        return $weekNum;
    }

    //获取年度价格(获取全部年的历史价格)
    public function yearPrice($id=0){
        $all=$this->_monthpricebygrabid($id);
        $year=array();
        foreach($all as $k=>$v){
            $tmpYear=substr($v['in_month'],0,4);
            $keys=array_keys($year);
            if($v['price']>0){
                if(in_array($tmpYear,$keys)){
                    $year[$tmpYear]['p']+=$v['price'];
                    $year[$tmpYear]['n']++;
                }else{
                    $year[$tmpYear]['p']=$v['price'];
                    $year[$tmpYear]['n']=1;
                }
            }
        }

        foreach($year as $k=>$v){
            $newAll[$k]=sprintf("%.2f",$v['p']/$v['n']);
        }
        unset($year,$all);
        return $newAll;
    }

    //获取季度价格(获取所有年份的季度价格)
    public function quarterPrice($id=0){
        $all=$this->_monthpricebygrabid($id);
        $quarter=array();
        foreach($all as $k=>$v){
            $q=$this->quarterInfo($v['in_month']);
            $keys=array_keys($quarter);
            if($v['price']>0){
                if(in_array($q,$keys)){
                    $quarter[$q]['p']+=$v['price'];
                    $quarter[$q]['n']++;
                }else{
                    $quarter[$q]['p']=$v['price'];
                    $quarter[$q]['n']=1;
                }
            }
        }

        foreach($quarter as $k=>$v){
            $newAll[$k]=sprintf("%.2f",$v['p']/$v['n']);
        }
        unset($quarter,$all);
        return $newAll;
    }

    //根据grab_id获取月价格数据
    private function _monthpricebygrabid($id){
        $monthModel=M('goods_price_month');
        $where['goods_price_id']=$id;
        $all=$monthModel->where($where)->select();
        return $all;
    }

    //识别当前日期属于那一年的哪一个季度
    public function quarterInfo($date){
        $year=substr($date,0,4);
        $month=substr($date,4,2);
        switch($month){
            case '01':
            case '02':
            case '03':
                $info=$year.'q1';
                break;
            case '04':
            case '05':
            case '06':
                $info=$year.'q2';
                break;
            case '07':
            case '08':
            case '09':
                $info=$year.'q3';
                break;
            case '10':
            case '11':
            case '12':
                $info=$year.'q4';
        }
        return $info;
    }

    //获取需要参与计算的周的开始日期和结束日期
    private function _weekfirsttoend(){
        //当前日期
        $sdefaultDate = date("Y-m-d");
        $year=substr($sdefaultDate,0,4);
        $month=substr($sdefaultDate,5,2);
        $day_num=date('j',strtotime($sdefaultDate));
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first=1;
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w=date('w',strtotime($sdefaultDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start=date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
        $week_start_year=substr($week_start,0,4);
        $week_start_month=substr($week_start,5,2);
        $week_start_day_num=date('j',strtotime($week_start));

        $allday=array();
        $weekNum=date('W',strtotime($sdefaultDate));
        //本周不跨月
        if($year==$week_start_year && $month==$week_start_month){
            for($i=$week_start_day_num;$i<=$day_num;$i++){
                $tmp=($i<10)?'0'.$i:$i;
                $allday[]=$year.$month.$tmp;
            }

            if($month=='12' && $weekNum=='01'){
                while($weekNum=='01'){
                    $weekNum=date('W',strtotime("-1 day"));
                }
            }
        }elseif($year==$week_start_year && $month!=$week_start_month){//本周跨月但不跨年
            //获取上月的天数
            $d=cal_days_in_month(CAL_GREGORIAN,($month-1),$year);
            for($i=$week_start_day_num;$i<=$d;$i++){
                $allday[]=$year.$week_start_month.$i;
            }

            for($i=1;$i<=$day_num;$i++){
                $tmp=($i<10)?'0'.$i:$i;
                $allday[]=$year.$month.$tmp;
            }
        }elseif($year!=$week_start_year && $month!=$week_start_month){//本周跨年
            for($i=1;$i<=$day_num;$i++){
                $tmp=($i<10)?'0'.$i:$i;
                $allday[]=$year.$month.$tmp;
            }
            $weekNum='01';
        }

        $data['day']=$allday;
        $data['weekNum']=$weekNum;
        return $data;
    }

    /******************获取价格变化幅度----开始*******************************/

    //获取（周，月，季度，年）价格变化幅度
    public function priceRange($id){
        $range=array();
        $nowPrice=M('GoodsPrice')->where('id='.$id)->find();
        //当前时间
        $year=date("Y");//当前年
        $month=date('n');//当前月
        $quarter=$this->quarterInfo(date('Ymd'));//当前季度
        $week=$this->_weekfirsttoend();//获取当前周

        //去年年份
        $preYear=$year-1;
        //全部年份历史价格
        $allYearPrice=$this->yearPrice($id);
        //获取年价格涨跌幅
        $range['year']=($allYearPrice && $allYearPrice[$preYear]>0)?$nowPrice['price']/$allYearPrice[$preYear]:0;

        //上个月月份
        if(($month-1)>0){//上个月没有跨年
            $tMonth=$month-1;
            $preMonth=$tMonth<10?'0'.$tMonth:$tMonth;
            $preMonth=$year.$preMonth;
        }else{//本月跨年
            $preMonth=$preYear.'12';
        }
        $monthPrice=M('goods_price_month')->where('goods_price_id='.$id." and in_month='".$preMonth."'")->find();
        $range['month']=($monthPrice && $monthPrice['price']>0)?$nowPrice['price']/$monthPrice['price']:0;

        //前一个季度
        $preQuarter=substr($quarter,-1,1);
        $preQuarter=($preQuarter=='1')?($year-1).'q4':$year.'q'.($preQuarter-1);//上季度跨年或者没跨年
        $quarterPrice=$this->quarterPrice($id);
        $range['quarter']=($quarterPrice && $quarterPrice[$preQuarter]>0)?$nowPrice['price']/$quarterPrice[$preQuarter]:0;

        //前一周
        if($week['weekNum']=='01'){//上一周跨年，取上一年的最后一周
            $preWeek=($year-1).$this->getTotalWeek($year-1);
        }else{//上一周未跨年，直接去上一周
            $preWeek=ltrim($week['weekNum'],'0')-1;
            $preWeek=($preWeek<10)?$year.'0'.$preWeek:$year.$preWeek;
        }
        $weekPrice=M('goods_price_week')->where('goods_price_id='.$id." and create_time='".$preWeek."'")->find();
        $range['week']=($weekPrice && $weekPrice['price']>0)?$nowPrice['price']/$weekPrice['price']:0;

        //格式化涨跌幅
        $newRange=$this->_formatrange($range);
        unset($allYearPrice,$monthPrice,$weekPrice,$quarterPrice);
        return $newRange;
    }

    /******************获取价格变化幅度----开始*******************************/

    //格式化价格涨跌幅度
    private function _formatrange($range){
        $newRange=array();
        foreach($range as $k=>$v){
            $tmp=$v-1;
            $tmp=($tmp!=0)?sprintf("%.3f",$tmp):0;
            $newRange[$k]=$tmp;
        }

        unset($range);
        return $newRange;
    }

}