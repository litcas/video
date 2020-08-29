<?php

namespace app\common\model;

class Roles extends Base {
	// 设置数据表（不含前缀）
	protected $name = 'roles';

	// 定义时间戳字段名
	protected $createTime = '';
	protected $updateTime = '';

	// 自动完成
	protected $auto = [];
	protected $insert = [];
	protected $update = [];

	/**
	 * 列表数据
	 * @param  [type]  $whereOr [description]
	 * @param  [type]  $where   [description]
	 * @param  [type]  $order   [description]
	 * @param  integer $page    [description]
	 * @param  integer $limit   [description]
	 * @param  integer $start   [description]
	 * @return [type]           [description]
	 */
	public function listData($whereOr = [], $where, $order, $page = 1, $limit = 20, $start = 0) {
		$limit_str = ($limit * ($page - 1) + $start) . "," . $limit;

		$field_a = 'id,role_name,status';

		$total = $this->where(function ($query) use ($whereOr) {
			$query->whereOr($whereOr);
		})->where($where)->limit($limit_str)->count();

		$list = $this->field($field_a)
			->where(function ($query) use ($whereOr) {
				$query->whereOr($whereOr);
			})
			->where($where)
			->order($order)->limit($limit_str)->select();

		return ['code' => 1, 'msg' => '数据列表', 'page' => $page, 'pagecount' => ceil($total / $limit), 'limit' => $limit, 'total' => $total, 'list' => $list];
	}

	/**
	 * 详情
	 * @param  [type] $where [description]
	 * @param  string $field [description]
	 * @return [type]        [description]
	 */
	public function infoData($where, $field = '*') {
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

	/**
	 * 保存或者更新
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function saveData($data) {
		$validate = \think\Loader::validate('rule');
		if (!$validate->check($data)) {
			return ['code' => 1001, 'msg' => '参数错误：' . $validate->getError()];
		}

		if (!empty($data['id'])) {
			$where = [];
			$where['id'] = ['eq', $data['id']];
			$data['update_time'] = time();
			$res = $this->allowField(true)->where($where)->update($data);
		} else {
			$data['add_time'] = time();
			$res = $this->allowField(true)->insert($data);
		}
		if (false === $res) {
			return ['code' => 1002, 'msg' => '保存失败：' . $this->getError()];
		}
		return ['code' => 1, 'msg' => '保存成功'];
	}

	/**
	 * 自动修改
	 * @param  [type] $where [description]
	 * @param  [type] $col   [description]
	 * @param  [type] $val   [description]
	 * @return [type]        [description]
	 */
	public function fieldData($ids, $col, $val) {
		if (empty($ids) || empty($col)) {
			return ['code' => 1001, 'msg' => '参数错误'];
		}

		$data = [];

		$data[$col] = $val;
		$data['update_time'] = time();

		$where['id'] = ['in', $ids];

		$res = $this->where($where)->update($data);
		if ($res === false) {
			return ['code' => 1002, 'msg' => '设置失败' . $this->getError()];
		}
		return ['code' => 1, 'msg' => '设置成功'];
	}

	/**
	 * 删除操作
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	public function delData($where) {
		$res = $this->where($where)->delete();
		if ($res === false) {
			return ['code' => 1001, 'msg' => '删除失败：' . $this->getError()];
		}
		return ['code' => 1, 'msg' => '删除成功'];
	}
}