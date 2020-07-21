<?php

namespace app\common\model;

use think\Db;
use think\Cache;
use think\helper\Arr;

class Video extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'video';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    public function listData($whereOr = [], $where, $order, $page = 1, $limit = 20, $start = 0)
    {
        $video_domain = Db::table('video_domain')->find();
        $video_examine = Db::table('video_examine')->column(null,'id');
        if (!is_array($where)) {
            $where = json_decode($where, true);
        }
        //a.id,a.type_pid,a.type_id,a.vod_name,a.vod_sub,a.vod_en,a.vod_tag,a.vod_pic,a.vod_pic_thumb,a.vod_pic_slide,a.vod_actor,a.vod_director,a.vod_writer,a.vod_behind,a.vod_blurb,a.vod_remarks,a.vod_pubdate,a.vod_total,a.vod_serial,a.vod_tv,a.vod_weekday,a.vod_area,a.vod_lang,a.vod_year,a.vod_version,a.vod_state,a.vod_duration,a.vod_isend,a.vod_douban_id,a.vod_douban_score,a.vod_time,a.vod_time_add,a.is_from,a.is_examine,a.vod_status,a.vod_time_auto_up
        //b.id,b.video_id,b.task_id,b.title,b.collection,b.vod_url,b.type,b.status,b.e_id,b.is_examine,b.resolution,b.bitrate,b.duration,b.size,b.time_up,b.time_auto_up
        $limit_str = ($limit * ($page - 1) + $start) . "," . $limit;
        $total = Db::name('Video')->where($where)->order($order)->limit($limit_str)->count();
//        $list = Db::name('Video')->where($where)->order($order)->limit($limit_str)->select();
        $sql = 'SELECT a.id as aid,a.type_pid,a.type_id,a.vod_name,a.vod_sub,a.vod_en,a.vod_tag,a.vod_pic,a.vod_pic_thumb,a.vod_pic_slide,a.vod_actor,a.e_id,a.vod_director,a.vod_writer,a.vod_behind,a.vod_blurb,a.vod_remarks,a.vod_pubdate,a.vod_total,a.vod_serial,a.vod_tv,a.vod_weekday,a.vod_area,a.vod_lang,a.vod_year,a.vod_version,a.vod_state,a.vod_duration,a.vod_isend,a.vod_douban_id,a.vod_douban_score,a.vod_time,a.vod_time_add,a.is_from,a.is_examine,a.vod_status,a.vod_time_auto_up,b.id as bid,b.video_id,b.task_id,b.title,b.collection,b.vod_url,b.type,b.status,b.e_id as b_eid,b.is_examine as b_is_examine,b.resolution,b.bitrate,b.duration,b.size,b.time_up,b.time_auto_up FROM  (
            SELECT  *
            FROM    video
            LIMIT  ' . $limit_str . '
        ) as `a` LEFT JOIN `video_collection` as  `b` ON `a`.`id`=`b`.`video_id` ORDER BY ' . $order . ' ';
        $list = Db::query($sql);
//        $id = 0;
        foreach ($list as $k_list => &$v_list) {

            if (substr_count($v_list['vod_pic'], 'http') == 0) {
                $v_list['vod_pic'] = $video_domain['img_domain'] . $v_list['vod_pic'];
            }
            if(!empty($v_list['vod_url'])){
                $v_list['vod_url'] = $video_domain['vod_domain'] . $v_list['vod_url'];
            }

            if(isset($video_examine[$v_list['b_eid'] ])){
                $v_list['b_reasons'] = $video_examine[$v_list['b_eid']];
            }
            if(isset($video_examine[$v_list['e_id'] ])){
                $v_list['a_reasons'] = $video_examine[$v_list['e_id']];
            }
            if ($v_list['collection'] <= 1) {
                $v_list['pid'] = 0;
                $v_list['m_reasons'] = $v_list['a_reasons'];
                $v_list['m_eid'] = $v_list['b_eid'];
                $v_list['m_status'] = $v_list['vod_status'];
            } else {
                $pid = $v_list['aid'];
                if(empty($pid)){
                    $pid = 0;
                }
                $v_list['pid'] = $pid;
                $v_list['m_eid'] = $v_list['e_id'];
                $v_list['m_reasons'] = $v_list['a_reasons'];
                $v_list['m_status'] = $v_list['status'];
            }
        }
//        p($list);
        return ['code' => 1, 'msg' => '数据列表', 'page' => $page, 'pagecount' => ceil($total / $limit), 'limit' => $limit, 'total' => $total, 'list' => $list];
    }

    public function listData1($where, $order, $page = 1, $limit = 20, $start = 0)
    {
        if (!is_array($where)) {
            $where = json_decode($where, true);
        }
        $limit_str = ($limit * ($page - 1) + $start) . "," . $limit;
        $total = Db::table('video_examine')->where($where)->order($order)->count();
        $list = Db::table('video_examine')->where($where)->order($order)->limit($limit_str)->select();
        return ['code' => 1, 'msg' => '数据列表', 'page' => $page, 'pagecount' => ceil($total / $limit), 'limit' => $limit, 'total' => $total, 'list' => $list];
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

        if (empty($num)) {
            $num = 20;
        }
        if ($start > 1) {
            $start--;
        }
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }
        if (!in_array($by, ['id', 'sort'])) {
            $by = 'id';
        }
        if (!empty($type)) {
            if ($type == 'current') {
                $type = intval($GLOBALS['type_id']);
            }
        }
        $where['type_id'] = $type;

        $order = $by . ' ' . $order;

        $cach_name = $GLOBALS['config']['app']['cache_flag'] . '_' . md5('vod_recommend_listcache_' . join('&', $where) . '_' . $order . '_' . $page . '_' . $num . '_' . $start);
        $res = Cache::get($cach_name);
        if ($GLOBALS['config']['app']['cache_core'] == 0 || empty($res)) {
            $res = $this->listData($where, $order, $page, $num, $start);
            $cache_time = $GLOBALS['config']['app']['cache_time'];
            if (intval($cachetime) > 0) {
                $cache_time = $cachetime;
            }
            if ($GLOBALS['config']['app']['cache_core'] == 1) {
                Cache::set($cach_name, $res, $cache_time);
            }
        }
        if ($res['list']) {
            foreach ($res['list'] as &$v) {
                if ($v['rel_ids']) {
                    $arr_vod_ids = explode(',', $v['rel_ids']);
                    $lp = [
                        'ids' => $v['rel_ids']
                    ];
                    $vods = array_column(model("Vod")->listCacheData($lp)['list'], null, 'vod_id');
                    foreach ($arr_vod_ids as $vod_id) {
                        // $v['rel_vod'][] = Arr::only($vods[$vod_id], ['vod_id', 'vod_name', 'vod_pic']);
                        if (isset($vods[$vod_id])) {
                            $v['rel_vod'][] = $vods[$vod_id];
                        }
                    }
                }

            }
            unset($v);
        }
        return $res;
    }

    public function infoData($where, $field = '*')
    {
        if (empty($where) || !is_array($where)) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }
        $info = $this->field($field)->where($where)->find();

        if (empty($info)) {
            return ['code' => 1002, 'msg' => '获取数据失败'];
        }
        $info = $info->toArray();

        return ['code' => 1, 'msg' => '获取成功', 'info' => $info];
    }

    public function saveData($data)
    {
        $data['down_time'] = time();
        if (!empty($data['id'])) {
            $where = [];
            $where['id'] = ['eq', $data['id']];
            unset($data['down_time']);
            $res = $this->allowField(true)->where($where)->update($data);
        } else {
            $res = $this->allowField(true)->insert($data);
        }
        if (false === $res) {
            return ['code' => 1002, 'msg' => '保存失败：' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if ($res === false) {
            return ['code' => 1001, 'msg' => '删除失败：' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '删除成功'];
    }

    public function fieldData($where, $col, $val)
    {
        if (!isset($col) || !isset($val)) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }

        $data = [];
        $data[$col] = $val;
        $res = $this->allowField(true)->where($where)->update($data);
        if ($res === false) {
            return ['code' => 1001, 'msg' => '设置失败：' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '设置成功'];
    }

}