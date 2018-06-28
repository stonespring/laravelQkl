<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StoneEncController
{
    /**
     *加密
     */
    public function StoneEnc($data,$key)
    {
        $time = time();  //当前时间
        $md5 = md5($data . $time . $key);  //md5加密
        $a = array();
        $b = array();
        $c = array();
        $arr = str_split($data . ',' . $time .'^'. $md5);
        foreach ($arr as $key => $value) {
            if ($key % 3 == 0) {
                $a[] = $key;
            } else if ($key % 2 == 0) {
                $b[] = $key;
            } else {
                $c[] = $key;
            }
        }
        $data = array();
        $d = array_reverse(array_merge($a, $b, $c));
        for ($i=0; $i < count($d); $i++) {
            $data[$d[$i]] = $arr[$d[$i]];
        }
        $data = implode("", $data);
        return base64_encode($data);

    }

    /**
     * 解密
     */
    public function StoneDec($data, $keyr)
    {
        //解密
        $a = array();
        $b = array();
        $c = array();
        $arr = str_split(base64_decode($data));
        foreach ($arr as $key => $value) {
            if ($key % 3 == 0) {
                $a[] = $key;
            } else if ($key % 2 == 0) {
                $b[] = $key;
            } else {
                $c[] = $key;
            }
        }
        $d = array_reverse(array_merge($a, $b, $c));
        $array = array();
        foreach ($d as $key => $value) {
            $array[$value] = $arr[$key];
        }
        $len = count($array);
        for ($i=0; $i < $len; $i++) {
            $arrays[$i] = $array[$i];
        }
        // }
        $arrays = implode('', $arrays);
        $md = explode('^', $arrays);
        $Md5 = $md[1];
        for ($i=0;$i < 10; $i++) {
            $base_Dec = md5(str_replace(',', '', $md[0]).$keyr.';'.$i);
            if ($base_Dec == $Md5) {
                $Dec_arr = md5(str_replace(',', '', $md[0]).$keyr.';'.$i);
            }
        }

        return $Dec_arr.'|'.$arrays;
    }
}
