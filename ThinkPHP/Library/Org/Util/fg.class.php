<?php
namespace Org\Util;
class fg{
   public function py_fg($query,$max_len=6) {
	  	$arr = 'a ai an ang ao ba pa bai ban bang bao bo bei ben beng bi bian biao bie bin bing bu ca cai can cang zang cao ce ceng cha chai chan chang chao zhao che chen cheng sheng chi shi chong chou chu chuai chuan chuang chui chun chuo ci cong cou cu cuan cui cun cuo da dai dan tan dang dao de deng di dian diao die ding diu dong tong dou du duan dui dun duo e wu en er fa fan pan fang fei fen feng fo fou fu pu ga gai gan gang gao ge ha gei gen geng gong gou gu gua guai guan guang gui gun guo hai han hang hao he hei hen heng hong hou hu hua huai huan huang hui hun huo ji jia jian kan jiang jiao jie ju jin jing jiong jiu zui juan jue jun ka kai kang kao ke ken keng kong kou ku kua kuai kuan kuang kui kun kuo la lai lan lang lao le lei ling leng li liang lian liao lie lin liu long lou lu lv luan lue lun luo ma mai man mang mao me mei men meng mi mian miao mie min ming miu mo mou mu na nai nan nang nao ne nei nen neng ni nian niang niao nie nin ning niu nong nu nv nuan nue nuo o ou pai pang pao pei pen peng pi pian piao pie pin ping po pou qi qia qian qiang qiao shao qie qin qing qiong qiu qu quan que qun ran rang rao re ren reng ri rong rou ru ruan rui run ruo sa sai san sang sao se sen seng sha shai shan shang she shen shou shu shua shuai shuan shuang shei shui shun shuo si song sou su suan sui sun suo ta tai tang tao te teng ti tian tiao tie ting tou tu tuan tui tun tuo wa wai wan wang wei wen weng wo xi xia xian xiang xiao xie xin xing xiong xiu xu xuan xue xun ya yan yang yao ye yi yin ying yo yong you yu yuan yue yun za zai zan zao ze zei zen zeng zha zhai zhan zhang zhe zhen zheng zhi zhong zhou zhu zhua zhuai zhuan zhuang zhui zhun zhuo zi zong zou zu zuan zun zuo pianpa ei m n dia cen nou ';
    	$arr = explode(' ',$arr);
    	//将字典键值反转
    	$dict = array_flip($arr);
        $feature = "";
	    $slen=mb_strlen($query,'UTF8');
	    $c_bg = 0;
	    while($c_bg<$slen){
	        $matched = false;
	        $c_len =(($slen-$c_bg)>$max_len)?$max_len:($slen-$c_bg);
	        $t_str = mb_substr($query, $c_bg,$c_len,'UTF8');
	        for($i=$c_len;$i>1;$i--){
	            $ttts = mb_substr($t_str, 0,$i,'UTF8');
	                if(!empty($dict[$ttts])){
	                    $matched = true;
	                    $c_bg += $i;
	                    $feature.='+'.$ttts.' ';
	                    break;
	                }
	        }
	        if(!$matched){
	            $c_bg++;
	        }
	    }   
	    $feature = rtrim($feature);
	    $goods = explode(' ',$feature);
	    $end = $goods[count($goods)-1];
	    $new = str_replace("+","",$end);
	    $goods[count($goods)-1] = $new;
	    $feature = implode(' ',$goods);
	    return $feature;

 	}
}

?>