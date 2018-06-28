<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CodeController as code;
use App\Http\Controllers\decController as dec;
use DB;
use QrCode;
class SharingController extends Controller
{

	//引入父类方法
	/*public function __construct(Request $request)
	{
        $this->msg = parent::exists_token($request);
	}*/


    /*
     * 获取分享链接
     * param@ uid 用户id
     */
    public function share_link(Request $request)
    {
    	$data = $request->all();
        if (!$data['token']) {
            code::code('1001');
        }

        $find = DB::table('admin_visit')->where('token', '=', $data['token'])->first();
        
        if ($find) {
            $qrcode = $this->create_link($find->u_id);
            return json_encode([
                'msg' => 'success',
                'data' => $qrcode
            ]);
        } else {
            return code::code('1012');
        }
    }

    /*
     * 生成分享链接
     * param@ uid 用户id
     */
    private function create_link($uid)
    {
        // $function = explode('::', __METHOD__)[1];
        $Invitation =dec::dec2s4($uid);
        $host = $_SERVER["HTTP_HOST"];
        $url = 'http://'.$host.'/get_invitation?invitation='.$Invitation;
        // $qrcode = $this->scerweima($url);
        return [
            'invitation' => $Invitation,
            'url' => $url
        ];
        // echo $Invitation;
        // echo $url;
    }

    /*
     * 生成二维码图片
     *
     */
    public function scerweima(Request $request){
        $data = $request->all();
        if ($data['url']) {
            $size = isset($data['size'])?$data['size']:200;
             return QrCode::size($size)->generate($data['url']);
        } else {
            return code::code('1020');
        }         
    }

    /*
     * 获取邀请码
     */
    function get_invitation(Request $request){
        $data = $request->all();
        if ($data['invitation']) {
            /*判断入库加一*/
            $uid = dec::s42dec($data['invitation']);

            //查询邀请次数
            $user_data = DB::table('admin_reception_users')->where('id', '=', $uid)->first();
            // var_dump($user_data->invitation_once);die;
            if ($user_data) {
                if ($user_data->invitation_once > 10) {
                    return code::code('1022');
                } else {
                    $set_invitation_once = DB::update('update admin_reception_users set invitation_once=invitation_once+1 where id='.$uid);
                    if ($set_invitation_once) {
                        return json_encode([
                            'msg' => 'success'
                        ]);
                    } else {
                        return code::code('1002');
                    }
                }
            } else {
                return code::code('1012');
            }            
        } else {
            return code::code('1021');
        }
    }
}