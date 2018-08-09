<?php

namespace main\app\ctrl\admin;

use main\app\ctrl\BaseAdminCtrl;
use main\app\ctrl\BaseCtrl;
use main\app\model\field\FieldModel;
use main\app\model\field\FieldTypeModel;

/**
 * 字段
 */
class Field extends BaseAdminCtrl
{

    public function index()
    {
        $data = [];
        $data['title'] = 'Users';
        $data['nav_links_active'] = 'issue';
        $data['sub_nav_active'] = 'issue_attribute';
        $data['left_nav_active'] = 'field';
        $this->render('gitlab/admin/field.php', $data);
    }

    public function fetchAll()
    {
        $model = new FieldModel();
        $data['fields'] = $model->getAllItems(false);

        $model = new FieldTypeModel();
        $data['field_types'] = $model->getAll(false);

        $this->ajaxSuccess('', $data);
    }

    public function get($id)
    {
        $id = (int)$id;
        $model = new FieldModel();
        $row = $model->getRowById($id);
        $row['options'] = json_decode($row['options']);
        $this->ajaxSuccess('ok', (object)$row);
    }

    /**
     * @param null $params
     * @throws \Exception
     */
    public function add($params = null)
    {
        if (empty($params)) {
            $this->ajaxFailed('错误', '没有提交表单数据');
        }
        $errorMsg = [];
        if (!isset($params['field_type_id']) || empty($params['field_type_id'])) {
            $errorMsg['field_type_id'] = '参数错误';
        }
        if (!isset($params['name']) || empty($params['name'])) {
            $errorMsg['name'] = '参数错误';
        }

        if (!empty($errorMsg)) {
            $this->ajaxFailed('参数错误', $errorMsg, BaseCtrl::AJAX_FAILED_TYPE_FORM_ERROR);
        }

        $info = [];
        $info['name'] = $params['name'];
        $info['type'] = $params['field_type_id'];
        $info['is_system'] = '0';
        if (isset($params['description'])) {
            $info['description'] = $params['description'];
        }
        if (isset($params['options'])) {
            $info['options'] = $params['options'];
        }
        $model = new FieldModel();
        if (isset($model->getByName($info['name'])['id'])) {
            $this->ajaxFailed('提示', '字段名称已经存在', BaseCtrl::AJAX_FAILED_TYPE_TIP);
        }

        list($ret, $msg) = $model->insert($info);
        if ($ret) {
            $this->ajaxSuccess('ok');
        } else {
            $this->ajaxFailed('服务器错误', '插入数据失败,详情:' . $msg);
        }
    }

    /**
     * 更新
     * @param array $params
     * @throws \Exception
     */
    public function update($params = [])
    {
        $id = null;
        if (isset($_GET['_target'][2])) {
            $id = (int)$_GET['_target'][2];
        }
        if (isset($_REQUEST['id'])) {
            $id = (int)$_REQUEST['id'];
        }
        if (!$id) {
            $this->ajaxFailed('错误', 'id不能为空');
        }

        $errorMsg = [];
        if (empty($params)) {
            $this->ajaxFailed('错误', '没有提交表单数据');
        }

        if (!isset($params['name']) || empty($params['name'])) {
            $errorMsg['name'] = '参数错误';
        }

        if (!empty($errorMsg)) {
            $this->ajaxFailed('参数错误', $errorMsg, BaseCtrl::AJAX_FAILED_TYPE_FORM_ERROR);
        }

        $id = (int)$id;

        $info = [];
        $info['name'] = $params['name'];
        if (isset($params['field_type_id'])) {
            $info['field_type_id'] = $params['field_type_id'];
        }
        if (isset($params['description'])) {
            $info['description'] = $params['description'];
        }
        if (isset($params['options'])) {
            $info['options'] = $params['options'];
        }

        $model = new FieldModel();
        $group = $model->getByName($info['name']);
        //var_dump($group);
        if (isset($group['id']) && ($group['id'] != $id)) {
            //$this->ajaxFailed('name_exists', [], 600);
        }

        $ret = $model->updateById($id, $info);
        if ($ret) {
            $this->ajaxSuccess('ok');
        } else {
            $this->ajaxFailed('server_error', [], 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        $id = null;
        if (isset($_GET['_target'][2])) {
            $id = (int)$_GET['_target'][2];
        }
        if (isset($_REQUEST['id'])) {
            $id = (int)$_REQUEST['id'];
        }
        if (!$id) {
            $this->ajaxFailed('错误', 'id不能为空');
        }
        $id = (int)$id;
        $model = new FieldModel();
        $ret = $model->deleteById($id);
        if (!$ret) {
            $this->ajaxFailed('服务器错误', '删除数据失败');
        } else {
            $this->ajaxSuccess('success');
        }
    }
}
