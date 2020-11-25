<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use File;
use Storage;
use Illuminate\Support\Facades\Log;

class CommonController extends Controller
{
    public static function fm_clean_path($path = '')
    {
        $path = trim($path);
        $path = trim($path, '\\/');
        $path = str_replace(array('../', '..\\'), '', $path);
        if ($path == '..') {
            $path = '';
        }
        return str_replace('\\', '/', $path);
    }

    public function showDateMonth($date = '', $format = 0)
    {
        $ret_date = $date;
        if (!in_array($date, array('', '0000-00-00'))) {
            list($year, $month, $day) = preg_split('/[-\/]/', $date);
            $thaiyear = $year + 543;
            $tmp_t_short = array("ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
            $tmp_t_long = array("มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
            if ($format == 0) {
                $ret_date = (int) $day . ' ' . $tmp_t_long[$month - 1]; // . ' ' . $thaiyear;
            } else if ($format == 2) {
                $y = substr($thaiyear, 2, 2);
                $ret_date = (int) $day . ' ' . $tmp_t_short[$month - 1]; // . ' ' . $y;
            } else {
                $ret_date = (int) $day . ' ' . $tmp_t_short[$month - 1]; // . ' ' . $thaiyear;
            }
        } else {
            $ret_date = '';
        }

        return $ret_date;
    }

}
