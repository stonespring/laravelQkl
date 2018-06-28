<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\CodeController as code;
use App\Http\Controllers\TokenController as token;

class TaskController extends TokenController
{

    //引入父类方法
    public function __construct(Request $request)
    {
        $this->msg = parent::exists_token($request);
    }

    /*
     * 新增任务
     * param@ name(varchar<50)任务名  describe(varchar<255)描述  state(tinyint)状态 img_src(varchar255)项目图标
     *
     */
    public function add_task(Request $request)
    {
        $data = $request->all();
        $arr = array();
        if (!$data['name']) {//任务名称
            code::code('1023');
        }

        if ($data['describe']) {//任务描述
            $arr['describe'] = $data['describe'];
        }

        if ($data['state']) {//如果不存在默认为1
            $arr['state'] = $data['state'];
        }

        if (!$data['img_src']) {//任务图标
            code::code('1025');
        }

        if ($data['type']) {//任务触发类型，不存在默认为1,1为click
            $arr['type'] = $data['type'];
        }

        if (!$data['power']) {//设置该任务奖励多少元力值
            code::code('1018');
        }

        if ($data['check_code']) {//检测行为状态码
            $arr['check_code'] = $data['check_code'];
        }

        if (!$data['url']) {//跳转的地址url
            $arr['url'] = $data['url'];
        }

        $get_insert_id = DB::table('admin_app')->insertGetId($arr);

        if ($get_insert_id) {
            return json_encode([
                'msg' => 'success'
            ]);
        }
    }


    /*
     * 签到领元石
     *
     *
     */
    function sing_in(Request $request) {
        $data = $request->all();
        if ($this->msg !== '{"msg":"success"}') {
            return code::code(1004);
        }
        $user_data = parent::get_header_token_uid();
        $get_newest_data = $this->get_task($user_data['uid']);

        //判断今天是否签到
        $user_date_time = strtotime($get_newest_data->created_at);
        $Tomorrow = strtotime(date('Y-m-d', time()+60*60*24));
        $Yesterday = strtotime(date('Y-m-d', time()));
        echo date('Y-m-d H:i;s', $Yesterday);
        if ($user_date_time > $Yesterday && $user_date_time < $Tomorrow) {
            return code::code('1027');
        }
        if ($get_newest_data) {
            //获取用户最新的元石数量
            $user_stone = $get_newest_data->stone_balance;
            //获取到增加元石的数量
            $stone = $get_newest_data->power;
            //增加到元石记录表
            $total_stone = intval($user_stone)+intval($stone);
            $arr = array(
                'uid' => $user_data['uid'],
                'appid' => $get_newest_data->appid,
                'resid' => $get_newest_data->resid,
                'actid' => $get_newest_data->actid,
                'stone' => $stone,
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time()),
                'stone_balance' => $total_stone,
            );
            $getid = DB::table('admin_stone')->insertGetId($arr);
            if ($getid) {
                return json_encode([
                    'msg' => 'success'
                ]);
            } else {
                return code::code('1002');
            }
            // echo $total_stone;
        }
    }


    /*
     * 根据uid获取到这个用户的元石以及行为
     *
     */
    private function get_task($uid) {
        return DB::table('admin_stone as s')
            ->orderby('s.updated_at', 'desc')
            ->leftjoin('admin_app as a', 's.appid', '=', 'a.app_id')
            ->where('s.uid', '=', $uid)
            ->where('a.state', '=', 1)
            ->first(['s.uid', 's.updated_at', 's.uid', 's.appid', 's.resid', 's.actid', 's.stone', 's.created_at', 's.stone_balance', 'a.url', 'a.power', 'a.type', 'a.img_src', 'a.name', 'a.describe', 'a.check_code']);
    }
}

