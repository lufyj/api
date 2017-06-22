<?php
/**
 * App端公司物流信息接口
 * Author: jingwei
 * Date: 2016/10/27
 */

namespace Api\Controller;

use Think\Controller;

class AppCompanyController extends BaseController {

    //认证商家信息(列表)
    public function comList() {
        if ($this->client_type == 1 || $this->client_type == 2) {
            $where[] = ' status=1';
            $param = [];
            $data = [];
            $post = I('post.');
            $minId = (int)($post['minId']);
            $num = (int)($post['num']);
            if ($minId) {
                $where[] = ' id <' . $minId;
            }
            //企业业务权限  1=>'检测',2=>'物流',5=>'仓库',6=>'直销',
            if (isset($post['bus']) && in_array($post['bus'], array(1, 2, 5, 6))) {
                $where[] = " business like '%" . intval($post['bus']) . "%'";
                $param['bus'] = (int)($post['bus']);
            } else {
                $param['bus'] = '';
            }

            if ($where) {
                $where = ' ' . implode(' and ', $where);
            } else {
                $where = '1=1';
            }

            $fields = 'id,uid,contacts as name,bl_address as address,mobile,business';
            $data = M('CompanyAuthen')->where($where)->order('id desc')->field($fields)->limit($num)->select();
            $companyInfo = M('Company');
            foreach ($data as $k => $v) {
                $logo = array();
                $logo = $companyInfo->field('logo_url,company_name')->where('uid=' . $v['uid'])->find();
                if ($logo['logo_url']) {
                    $data[$k]['logo'] = $logo['logo_url'];
                } else {
                    if ($this->client_type == 1) {
                        $data[$k]['logo'] = '/Public/Home/images/app1/logo.png';
                    } else {
                        $data[$k]['logo'] = '/Public/Home/images/app2/logo@2x.png';
                    }
                }
                $data[$k]['name'] = $logo['company_name'] ? $logo['company_name'] : $v['name'];
            }
            if ($data) {
                $this->ajaxDie(1, $data);
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    /*认证商家信息(详情)*/
    public function comInfo() {
        if ($this->client_type == 1 || $this->client_type == 2) {
            $post = I('post.');
            if ((int)$post['id'] > 0) {
                $info = array();
                $where['status'] = 1;
                $where['id'] = (int)$post['id'];
                $authenInfo = D('CompanyAuthen')->field('uid,contacts,bl_address,mobile')->where($where)->find();
                if ($authenInfo) {
                    $comInfo = D('Company')->field('company_name,memos,banner_url,logo_url')->where('uid=' . $authenInfo['uid'])->find();
                    $info = array();
                    $info['name'] = $comInfo['company_name'] ? $comInfo['company_name'] : $authenInfo['contacts'];
                    $info['address'] = $authenInfo['bl_address'];
                    $info['mobile'] = $authenInfo['mobile'];
                    $info['desc'] = $comInfo['memos'] ? $comInfo['memos'] : '';
                    if ($this->client_type == 1) {
                        $logo = '/Public/Home/images/app1/pic_logo.png';
                        $background = '/Public/Home/images/app1/pic_bg.png';
                    } else {
                        $logo = '/Public/Home/images/app2/pic_logo@2x.png';
                        $background = '/Public/Home/images/app2/pic_bg@2x.png';
                    }
                    $info['background'] = $comInfo['banner_url'] ? $comInfo['banner_url'] : $background;
                    $info['logo'] = $comInfo['logo_url'] ? $comInfo['logo_url'] : $logo;
                    unset($authenInfo, $comInfo);
                    $this->ajaxDie(1, $info);

                } else {
                    $this->ajaxDie(0);
                }
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //物流信息(列表)
    public function deliveryList() {

        if ($this->client_type == 1 || $this->client_type == 2) {
            $where = [];
            $info = array();
            $post = I('post.');
            $minId = intval($post['minId']);
            $num = intval($post['num']);
            if ($minId) {
                $where[] = ' id <' . $minId;
            }

            if (isset($post['t']) && in_array($post['t'], array(0, 1, 2, 3, 4))) {
                $where[] = 'type=' . intval($post['t']);
                $info['param']['t'] = intval($post['t']);
            } else {
                $info['param']['t'] = 9999;
            }

            if ($post['be'] && ctype_digit($post['be']) && substr($post['be'], 0, 1) != 0 && (strlen($post['be']) == 2 || strlen($post['be']) == 4 || strlen($post['be']) == 6)) {
                $info['param']['be'] = $post['be'];
                $where[] = "begin like '" . $post['be'] . "%'";
            }

            if ($post['en'] && ctype_digit($post['en']) && substr($post['en'], 0, 1) != 0 && (strlen($post['en']) == 2 || strlen($post['en']) == 4 || strlen($post['en']) == 6)) {
                $info['param']['end'] = $post['en'];
                $where[] = "end like '" . $post['en'] . "%'";
            }

            if ($where) {
                $where = ' ' . implode(' and ', $where);
            } else {
                $where = '1=1';
            }

            $delivery = D('CompanyDelivery');
            $data = $delivery->where($where)->order('id desc')->limit($num)->select();
            foreach ($data as $k => $v) {
                $v['begin'] = sliceCode($v['begin']);
                $data[$k]['begin'] = $this->getZoneInfo($v['begin']);
                $v['end'] = sliceCode($v['end']);
                $data[$k]['end'] = $this->getZoneInfo($v['end']);
                $data[$k]['type'] = $this->getCarType(intval($v['type']));
                $data[$k]['add_time'] = date('Y-m-d', $v['add_time']);
            }
            $info['data'] = $data;
            if ($info) {
                $this->ajaxDie(1, $info);
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //物流详情
    public function deliveryInfo() {

        if ($this->client_type == 1 || $this->client_type == 2) {

            $post = I('post.');
            if (intval($post['id']) > 0) {
                $delivery = D('CompanyDelivery');
                $id = intval($post['id']);
                $data = $delivery->where('id=' . $id)->find();
                $data['begin'] = sliceCode($data['begin']);
                $data['begin'] = $this->getZoneInfo($data['begin']);
                $data['end'] = sliceCode($data['end']);
                $data['end'] = $this->getZoneInfo($data['end']);
                $data['type'] = $this->getCarType($data['type']);
                if ($data) {
                    $this->ajaxDie(1, $data);
                } else {
                    $this->ajaxDie(0);
                }
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //仓库信息(列表)
    public function storeList() {

        if ($this->client_type == 1 || $this->client_type == 2) {

            $where = [];
            $info = array();
            $post = I('post.');
            $minId = intval($post['minId']);
            $num = intval($post['num']);
            if ($minId) {
                $where[] = ' id <' . $minId;
            }

            if (isset($post['t']) && in_array($post['t'], array(0, 1, 2, 3, 4))) {
                $where[] = 'type=' . intval($post['t']);
                $info['param']['t'] = intval($post['t']);
            } else {
                $info['param']['t'] = 9999;
            }

            if ($post['add'] && ctype_digit($post['add']) && substr($post['add'], 0, 1) != 0 && (strlen($post['add']) == 2 || strlen($post['add']) == 4 || strlen($post['add']) == 6)) {
                $info['param']['add'] = $post['add'];
                $where[] = "zone like '" . $post['add'] . "%'";
            }
            if ($where) {
                $where = ' ' . implode(' and ', $where);
            } else {
                $where = '1=1';
            }

            $store = D('CompanyStore');
            $data = $store->field('id,user_id,type,size,height,address,contacts,mobile,img')->where($where)->order('id desc')->limit($num)->select();
            foreach ($data as $k => $v) {
                $data[$k]['type'] = $this->getStoreType(intval($v['type']));
                $tmp = array();
                if ($v['img']) {
                    $tmp = explode(',', $v['img']);
                } else {
                    $tmp[0] = '/Public/Home/images/noimg.png';
                }
                $data[$k]['img'] = $tmp[0];
            }
            $info['data'] = $data;
            if ($info) {
                $this->ajaxDie(1, $info);
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //仓库信息详情
    public function storeInfo() {

        if ($this->client_type == 1 || $this->client_type == 2) {

            $post = I('post.');
            if (intval($post['id']) > 0) {
                $store = D('CompanyStore');
                $id = intval($post['id']);
                $data = $store->where('id=' . $id)->find();
                $data['zone'] = sliceCode($data['zone']);
                $data['zone'] = $this->getZoneInfo($data['zone']);
                $data['address'] = $data['zone'] . $data['address'];
                $data['imgs'] = explode(',', $data['img']);
                //$data['type']=$this->getStoreType(intval($data['type']));
                if (empty($data['img'])) {
                    $data['imgs'] = array();
                }
                unset($data['img']);
                if ($data) {
                    $this->ajaxDie(1, $data);
                } else {
                    $this->ajaxDie(0);
                }
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //加工详情
    public function processInfo() {

        if ($this->client_type == 1 || $this->client_type == 2) {
            $post = I('post.');
            if (intval($post['id']) > 0) {
                $process = M('CompanyProcess');
                $id = intval($post['id']);
                $data = $process->where('id=' . $id . ' and type=1')->find();
                if ($data) {
                    $this->ajaxDie(1, $data);
                } else {
                    $this->ajaxDie(0);
                }
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //检测详情
    public function checkInfo() {
        if ($this->client_type == 1 || $this->client_type == 2) {
            $post = I('post.');
            if (intval($post['id']) > 0) {
                $check = M('CompanyCheck');
                $id = intval($post['id']);
                $data = $check->where(['id' => $id])->find();
                if ($data) {
                    $this->ajaxDie(1, $data);
                } else {
                    $this->ajaxDie(0);
                }
            } else {
                $this->ajaxDie(0);
            }
        } else {
            $this->ajaxDie(43);
        }
    }

    //获取行政区划信息
    private function getZoneInfo($code = array()) {
        if (empty($code)) {
            return '全国';
        }

        $str = '';
        $region = D('Region');
        $fileds = array('REGION_ID', 'REGION_CODE', 'REGION_NAME');
        foreach ($code as $k => $v) {
            $info = array();
            if ($v != 0) {
                $info = $region->where('REGION_CODE=' . $v)->field($fileds)->find();
                $str .= $info['region_name'];
            }
        }
        return $str;
    }

    //获取仓库类型
    private function getStoreType($code) {
        $type = '普通';
        switch ($code) {
            case 0:
                $type = '普通';
                break;
            case 1:
                $type = '冷藏';
                break;
            case 2:
                $type = '恒温/保温';
                break;
            case 3:
                $type = '特种';
                break;
            case 4:
                $type = '气调';
                break;
            default:
                $type = '普通';
                break;
        }
        return $type;
    }

    //获取车辆类型
    public function getCarType($type) {
        switch ($type) {
            case 0:
                $type = '厢式/板车';
                break;
            case 1:
                $type = '集装箱';
                break;
            case 2:
                $type = '冷藏车';
                break;
            case 3:
                $type = '危险品车辆';
                break;
            case 4:
                $type = '特种车辆';
                break;
            default:
                $type = '厢式/板车';
                break;
        }
        return $type;
    }
}
