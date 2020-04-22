<?php
namespace app\api\controller;
use think\Controller;
use think\Cache;

class Index extends Base{

    private $sort = "vod_score desc,vod_time_add desc";

    public function __construct()
    {
        parent::__construct();
        $this->_param = input();
    }

    // 首页导航
    public function home_nav(){
        $lp = [
            'type_status'   => 1,
            'type_pid'      => 0,
        ];
        $list  = model("Type")->listData($lp,"type_id asc");

        $data[] = ['id' => 0, 'name' => "推荐", 'img' => "",];
        $list = $list['list'] ?? [];
        $array = [];

        foreach($list as $key=>$item){
            $array[$key]['id']      = $item['type_id'];
            $array[$key]['name']    = $item['type_name'];
            $array[$key]['img']     = $item['img'] ?? "";
            $array[$key]['msg']     = type_extend($item['type_extend']) ?? [];
        }
        $list = array_merge($data, $array);
        return json_return($list);
    }

    // 首页导航下数据
    public function home_data(){
        $id = $this->_param['id'] ?? 0;

        // 轮播图
        $lp = [
            'status'   => 1,
            'type_id'  => $id,
        ];
        $bannel = model("Banner")->listData($lp,"sort desc");
        $bannel = $bannel['list'] ?? [];
        $getSlide = [];
        foreach($bannel as $k=>$item){
            $getSlide[$k]['id']     = $item['id'];
            $getSlide[$k]['name']   = $item['name'];
            $getSlide[$k]['img']    = imageDir($item['img']);
            $getSlide[$k]['url']    = $item['link'];
            $getSlide[$k]['type']   = 1;
        }

        $getListBlock = [];
        // 内容
        if ($id == 0){
            $getListBlock = $this->tuijian();
        }else{
            $fatherRes =  [];
            if(in_array($id,[3,4])){
                $fatherRes =  model("Type")->listData(['type_id' => $id],"type_sort desc");
                $fatherRes = $fatherRes['list'] ?? [];
            }

            $where = [
                'type_pid'   => $id
            ];
            $sonRes =  model("Type")->listData($where,"type_sort desc");
            $sonRes = $sonRes['list'] ?? [];

            $res = array_merge($sonRes,$fatherRes);
            foreach($res as $item){
                $r = $item["type_id"];
                $d = array(
                    'name' => $item['type_name'],
                    'data' => $this->getVodList($r,6,1),
                );

                array_push($getListBlock,$d);
            }

            // 电影、电视剧 加上精品推荐
            if(in_array($id,[1,2])){
                $doubanRecomData = [];
                $where = [
                    'type_id'   => ['eq',$id],
                    'status'    => ['eq','1'],
                    'vod_id'    => ['neq','0'],
                ];
                // 电影取三条
                $doubanList  = model("douban_recommend")->field('vod_id')->where($where)->order('id asc')->limit(6)->select();
                $doubanIds  = implode(",",array_column($doubanList,'vod_id'));
                $doubanRecomData[] = [
                    'name'  => "精品推荐",
                    'data'  => $this->vodStrData($doubanIds),
                ];

                $getListBlock = array_merge($doubanRecomData,$getListBlock);
            }
        }

        $list = array(
            'name'  => '',
            'slide' => $getSlide,
            'video' => $getListBlock,
        );

        return json_return($list);
    }

    // 推荐 猜你在追 + 豆瓣推荐  + 今日推荐 + 热门
    public function tuijian(){
        // 猜你在追
        $guessDatas = [];
        $guessData = $this->guessUserMovies();
        if($guessData){
            $guessDatas[] = [
                'type'  => 3,
                'id'    => 0,
                'msg'   => "",
                'name'  => "猜你在追",
                'data'  => $guessData,
            ];
        }

        // 豆瓣推荐
        $doubanData = $this->doubanRecom();

        // 后台推荐配置
        $tuijianData = [];
        $tuijian = model("VodRecommend")->listData(['status' => 1,"type_id" => 0], "sort desc" );
        $tuijian = $tuijian['list'] ?? [];
        foreach($tuijian as $item){
            $tuijianData[] = [
                'type'  => 2,
                'id'    => 0,
                'msg'   => "",
                'name'  => $item['name'],
                'data'  => $this->vodStrData($item['rel_ids']),
            ];
        }

        // 本地热门
        $data = [
            [
                'type'  => 1,
                'id'    => 1,
                'msg'   => json_encode(getScreen(1),JSON_UNESCAPED_UNICODE),
                'name'  => '热播电影',
                'data'  => $this->getVodList(1,6,1),
            ],
            [
                'type'  => 1,
                'id'    => 2,
                'msg'   => json_encode(getScreen(2),JSON_UNESCAPED_UNICODE),
                'name'  => '热播剧',
                'data'  => $this->getVodList(2,6,1),
            ],
            [
                'type'  => 1,
                'id'    => 3,
                'msg'   => json_encode(getScreen(3),JSON_UNESCAPED_UNICODE),
                'name'  => '热播综艺',
                'data'  => $this->getVodList(3,6,1),
            ],
            [
                'type'  => 1,
                'id'    => 4,
                'msg'   => json_encode(getScreen(4),JSON_UNESCAPED_UNICODE),
                'name'  => '热播动漫',
                'data'  => $this->getVodList(4,6,1),
            ],
        ];

        $data = array_merge($guessDatas,$doubanData,$tuijianData,$data);

        return $data;
    }

    // 豆瓣推荐
    public function doubanRecom($limit = 3){
        $doubanData = [];
        $model = model("douban_recommend");
        $where = [
            'status'    => ['eq','1'],
            'vod_id'    => ['neq','0'],
        ];
        // 电影取三条
        $ids  = $model->field('vod_id')->where(array_merge($where,['type_id'=>['eq',1]]))->order('id asc')->limit($limit)->select();
        $ids2 = $model->field('vod_id')->where(array_merge($where,['type_id'=>['eq',2]]))->order('id asc')->limit($limit)->select();
        $ids  = objectToArray($ids);
        $ids2 = objectToArray($ids2);

        $doubanList = array_merge($ids,$ids2);
        $doubanIds  = implode(",",array_column($doubanList,'vod_id'));

        $doubanData[] = [
            'type'  => 4,
            'id'    => 0,
            'msg'   => "",
            'name'  => "精品推荐",
            'data'  => $this->vodStrData($doubanIds),
        ];

        return $doubanData;
    }

    // 豆瓣推荐列表
    public function recomData(){
        $page  = $this->_param['page'] ?? 1;
        $limit = 18;
        $pageSize = ($page - 1) * $limit;

        $where = [
            'r.status'    => ['eq','1'],
            'r.vod_id'    => ['neq','0'],
        ];

        $list  = model("douban_recommend")
            ->alias('r')
            ->field('r.vod_id,r.name,v.vod_pic,v.vod_score,v.vod_remarks')
            ->join('vod v','r.vod_id = v.vod_id','left')
            ->where($where)
            ->order('id asc')
            ->limit($pageSize,$limit)
            ->select();
        $list  = objectToArray($list);

        $data = [];
        foreach($list as &$item){
            $data[] = [
                'img'   => imageDir($item['vod_pic']),
                'id'    => $item['vod_id'],
                'name'  => $item['name'],
                'score' => $item['vod_score'],
                'msg'   => $item['vod_remarks'],
            ];
        }

        return json_return($data);
    }

    // 分类视频
    public function getVodList($id,$limit,$page){
        $lp = [
            'type_id'   => $id,
        ];
        $info = model("Vod")->listData($lp, $this->sort, $page, $limit);

        $info = $info['list'] ?? [];
        $array = array();
        foreach($info as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'id'    => $r['vod_id'],
                'name'  => $r['vod_name'],
                'score' => $r['vod_score'],
                'msg'   => $r['vod_remarks'],
            );
            array_push($array,$d);
        }
        return $array;
    }

    // 分类视频
    public function vodStrData($vodIds,$limit = 6){
        $vodIds = explode(",",$vodIds);

        $lp = [
            'vod_id'   => ['in',$vodIds],
        ];
        $info = model("Vod")->listData($lp,  $this->sort, 1, $limit);

        $info = $info['list'] ?? [];
        $array = array();
        foreach($info as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'id'    => $r['vod_id'],
                'name'  => $r['vod_name'],
                'score' => $r['vod_score'],
                'msg'   => $r['vod_remarks'],
            );
            array_push($array,$d);
        }
        return $array;
    }

    // 详情
    public function vod(){
        $id = $this->_param['id'] ?? 0;

        $where['vod_status'] = array('eq', 1);
        $where['vod_id']     = array('eq', $id);
        $info = model("Vod")->infoData($where);
        $info = $info['info'] ?? [];

        $data = array(
            'name'      => $info["vod_name"],
            'img'       => imageDir($info["vod_pic"]),
            'msg'       => $info["vod_remarks"],
            'score'     => $info["vod_score"],
            'type'      => $info["vod_area"] ,
            'info'      => $info["vod_content"],
            'playcode'  => $info["vod_play_from"],
            'playlist'  => $info["vod_play_url"],
            'downcode'  => $info["vod_down_from"],
            'downlist'  => $info["vod_down_url"],
        );
        return json_return($data);
    }

    // 搜索
    public function search(){
        $key  = $this->_param['key'] ?? "";
        $page = $this->_param['page'] ?? 1;
        $where = [
            "vod_name|vod_sub|vod_actor|vod_director"  => ["like", '%'.$key.'%'],
        ];
        $res = model("Vod")->listData($where, $this->sort, $page, 18);
        $res = $res['list'] ?? [];

        $data = [];
        foreach($res as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'name'  => $r['vod_name'],
                'msg'   => $r['vod_remarks'],
                'text'  => $r['vod_content'],
                'url'   => $r['vod_id'],
            );
            array_push($data,$d);
        }
        return json_return($data);
    }

    // 热搜关键词
    public function search_hot(){
        $douban = model('douban_recommend');

        $data = [];
        for($i = 1; $i <= 2; $i++){
            $where = [
                'type_id'   => ['eq',$i],
                'status'    => ['eq','1'],
                'vod_id'    => ['neq','0'],
            ];
            $list = $douban->field("name")->where($where)->limit(10)->order('id asc')->select();
            $list = objectToArray($list);
            $data = array_merge($data,array_column($list,'name'));
        }

//        $config = config('maccms.app');
//        $search_hot = $config['search_hot'] ?? [];
//        $list = explode(",",$search_hot);

        return json_return($data);
    }

    // 筛选
    public function screen(){
        $id     = $this->_param['id'] ?? 0;
        $page   = $this->_param['page'] ?? 1;
        $type   = $this->_param['type'] ?? "";
        $area   = $this->_param['area'] ?? "";
        $year   = $this->_param['year'] ?? "";

        $type = $type == "类型" ? "" : $type;
        $area = $area == "地区" ? "" : $area;
        $year = $year == "年份" ? "" : $year;

        $where = [];
        if($id == 1 || $id == 2 ){
            // 电影电视剧 有一级分类
            $where['type_id_1']   = ['eq',$id];
        }else{
            $where['type_id']   = ['eq',$id];
        }

        if($type != ""){
            $where['vod_class']   = ['like','%'.$type.'%'];
        }

        if($area != ""){
            if($area == "国产" || $area == "大陆" ){
                $where['vod_area']   = ['in',["中国大陆","大陆","中国"]];
            }else{
                $where['vod_area']   = ['eq',$area];
            }
        }

        if($year != ""){
            $where['vod_year']   = ['eq',$year];
        }

        $info = model("Vod")->listData($where, $this->sort, $page, 18);
        $info = $info['list'] ?? [];

        $array = array();
        foreach($info as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'id'    => $r['vod_id'],
                'name'  => $r['vod_name'],
                'score' => $r['vod_score'],
                'msg'   => $r['vod_remarks'],
            );
            array_push($array,$d);
        }
        return json_return($array);
    }

    // 用户记录日志
    public function userLog(){
        // 用户注册
        $mac    = $this->_param['mac'] ??  "" ;
        $type   = $this->_param['type'] ??  "" ;
        $rid    = $this->_param['rid'] ??  "" ;
        $sid    = $this->_param['sid'] ??  "" ;
        $nid    = $this->_param['nid'] ??  "" ;

        if($mac == "" || $type == "" || $rid == ""){
            return json_return("参数错误");
        }

        $userModel = model("User");
        $userRes = $userModel->infoData(['user_name' => $mac],"user_id");

        if($userRes['code'] == 1002){
            $userModel->saveData([
                'user_name' => $mac,
                'user_pwd'  => "123456",
            ]);
            $userId = $userModel::getLastInsID();
        }else{
            $userId = $userRes['info']['user_id'];
        }

        $data = [
            'user_id'    => $userId ,   // 用户ID
            'ulog_mid'   => 1 ,         // 模块 1视频 2文章 3专题 8演员
            'ulog_type'  => $type ,     // 类型 1浏览 2收藏 3想看 4点播 5下载
            'ulog_rid'   => $rid ,      // 关联ID ：视频ID
            'ulog_sid'   => $sid ,      // 来源 ：播放源
            'ulog_nid'   => $nid ,      // 第几集
        ];
        $res = model("Ulog")->saveData($data);
        if($res['code'] != 1){
            return json_return(['记录日志失败']);
        }

        return json_return(['保存成功']);
    }

    // 猜你在追电视剧
    public function guessUserMovies(){
        $mac    = $this->_param['mac'] ??  "" ;
        if($mac == ""){
            return [];
        }

        // 查询用户信息
        $where = [
            'user_name' => $mac,
        ];
        $userInfo = model("User")->infoData($where,"user_id");
        $userInfo = $userInfo['info'] ?? [];
        if($userInfo == ""){
            return [];
        }
        $userId = $userInfo['user_id'] ?? 0;

        // 查询用户 最近日志信息
        $logWhere = [
            'user_id'   => ['eq',$userId],
            'ulog_mid'  => ['eq',1],
            'ulog_type' => ['in',[2,3,4,5]],
        ];
        $userLog = model("Ulog")->field("ulog_rid")->where($logWhere)->order("ulog_time desc")->select();
        $userLog = objectToArray($userLog);

        // 获取去重ids
        $rids = array_unique(array_column($userLog,"ulog_rid"));

        $field = "vod_id,vod_name,vod_pic,vod_score,vod_remarks";
        $vodList = model("Vod")->field($field)->where(['vod_id'=>['in',$rids]])->order("vod_time_add desc")->select();
        $vodList = objectToArray($vodList);

        // 获取相应 影视数据
        $array = [];
        foreach($vodList as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'id'    => $r['vod_id'],
                'name'  => $r['vod_name'],
                'score' => $r['vod_score'],
                'msg'   => $r['vod_remarks'],
            );
            array_push($array,$d);
        }
        return $array;
    }

    // 相似关联 影视
    public function relation(){
        $id    = $this->_param['id'] ??  "" ;
        if($id == ""){
            return json_return([]);
        }

        $model  = model("vod");
        $lp = [
            'vod_id' => $id
        ];
        $info = $model->field("vod_id,type_id,type_id_1,vod_actor")->where($lp)->find();
        $info = objectToArray($info);

        $actor = explode(',',$info['vod_actor']) ?? "";
        $actor = $actor[0] != "" ? $actor[0] : "";
        $field = "vod_id,vod_name,vod_pic,vod_score,vod_remarks";

        $where = [
            'vod_id'    => ['neq',$id],
            'vod_actor' => ['like', "%".$actor."%"],
        ];

        if($info['type_id'] == 1 || $info['type_id'] == 2   ) {
            $where['type_id|type_id_1'] = ['eq',$info['type_id']];
        }else if($info['type_id'] == 3 || $info['type_id'] == 4   ){
            $where['type_id'] = ['eq',$info['type_id']];
        }else if($info['type_id'] > 4){
            $where['type_id_1'] = ['eq',$info['type_id_1']];
        }

        $res = $model->field($field)->where($where)->order('vod_time_add desc')->limit(6)->select();
        $res = objectToArray($res);

        $count = count($res);
        if($count < 6){
            unset($where['vod_actor']);
            $limit = $count >= 6 ? 6 : 6 - $count;
            $res2 = $model->field($field)->where($where)->order('vod_time_add desc')->limit($limit)->select();
            $res = array_merge($res,$res2);
        }

        $res = objectToArray($res);

        $array = [];
        foreach($res as $r){
            $d = array(
                'img'   => imageDir($r['vod_pic']),
                'id'    => $r['vod_id'],
                'name'  => $r['vod_name'],
                'score' => $r['vod_score'],
                'msg'   => $r['vod_remarks'],
            );
            array_push($array,$d);
        }
        return json_return($array);
    }

}
