<?php
namespace app\common\model;
use think\Db;
use think\Cache;

class Banner extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'banner';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function listData($where,$order,$page=1,$limit=20,$start=0)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        $total = $this->where($where)->count();
        $list = Db::name('Banner')->where($where)->order($order)->limit($limit_str)->select();

        //分类
        if(mac_get_type_list() != false){
            $new_table =  mac_get_type_list_model();
            $model =  new \app\common\model\TypeSeo($new_table);
            $type_list = $model->getCache();
        }else{
            $type_list = model('Type')->getCache('type_list');
        }
        foreach($list as $k=>$v){
            if(!empty($v['type_id'])) {
                $list[$k]['type'] = $type_list[$v['type_id']];
            } else {
                $list[$k]['type'] = ['type_name' => '首页'];
            }
        }
        // 关联视频
        foreach($list as $k=>$v){
            $list[$k]['vod'] = [];
            if ($v['rel_vod']) {
                $vod = model('Vod')->infoData(['vod_id'=>$v['rel_vod']]);
                if ($vod['code'] == 1) {
                    $list[$k]['vod'] = $vod['info'];
                }
            }
        }

        return ['code'=>1,'msg'=>'数据列表','page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
    }

    public function listCacheData($lp)
    {
        if (!is_array($lp)) {
            $lp = json_decode($lp, true);
        }

        $order = $lp['order'];
        $by = $lp['by'];
        $type = $lp['type'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $cachetime = $lp['cachetime'];

        $page = 1;
        $where = [];

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }
        if (!in_array($by, ['id', 'sort'])) {
            $by = 'id';
        }
        if (!empty($type)) {
            if($type=='current'){
                $type = intval( $GLOBALS['type_id'] );
            }
        }
        $where['type_id'] = $type;

        $order = $by . ' ' . $order;

        $cach_name = $GLOBALS['config']['app']['cache_flag']. '_' . md5('banner_listcache_'.join('&',$where).'_'.$order.'_'.$page.'_'.$num.'_'.$start);
        $res = Cache::get($cach_name);
        if($GLOBALS['config']['app']['cache_core']==0 || empty($res)) {
            $res = $this->listData($where, $order, $page, $num, $start);
            $cache_time = $GLOBALS['config']['app']['cache_time'];
            if(intval($cachetime)>0){
                $cache_time = $cachetime;
            }
            if($GLOBALS['config']['app']['cache_core']==1) {
                Cache::set($cach_name, $res, $cache_time);
            }
        }
        if ($res['list']) {
            foreach ($res['list'] as &$v) {
                $v['img'] = mac_url_img($v['img']);
            }
            unset($v);
        }
        return $res;
    }

    public function infoData($where,$field='*')
    {
        if(empty($where) || !is_array($where)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }
        $info = $this->field($field)->where($where)->find();

        if(empty($info)){
            return ['code'=>1002,'msg'=>'获取数据失败'];
        }
        $info = $info->toArray();

        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Banner');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }

        $data['time'] = time();
        if(!empty($data['id'])){
            $where=[];
            $where['id'] = ['eq',$data['id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $res = $this->allowField(true)->insert($data);
        }
        if(false === $res){
            return ['code'=>1002,'msg'=>'保存失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>'删除失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'删除成功'];
    }

    public function fieldData($where,$col,$val)
    {
        if(!isset($col) || !isset($val)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }

        $data = [];
        $data[$col] = $val;
        $res = $this->allowField(true)->where($where)->update($data);
        if($res===false){
            return ['code'=>1001,'msg'=>'设置失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'设置成功'];
    }

}