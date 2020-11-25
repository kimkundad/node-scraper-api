<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentDetail;
use App\Models\DirList;
// use \stdClass;
use Illuminate\Support\Facades\DB;
use Route;

class SystemInfoController extends Controller
{
    // private $columns;

    public function __construct()
    {
        // $this->columns = 'javascript:void(0);';
    }

    public function routeList()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) { return $route->uri(); });
        $routeDatas = array();

        // foreach ($routeCollection as $value) {
        //     echo $value->getPath();
        // }

        if (count($routes) > 0) {
            foreach($routes as $route) {
                $ignition = strpos($route, '_ignition');
                if ($ignition === false && $route != '/') {
                    $apiType = strpos($route, 'api/');
                    $rType = ($apiType === false) ? 'WEB' : 'API';
                    $detail = $this->routeDetail($route);
                    $routeDatas[] = array('type' => $rType, 'link' => $route, 'api' => $detail);
                }
            }
        }

        return $routeDatas;
    }

    public function tableList()
    {
        $tableList = array();

        $tables = DB::select('SHOW TABLES');
        foreach($tables as $table){
            $tableList[] = $table->Tables_in_node_scraper;
        }

        return $tableList;
    }

    public function routeDetail($route = '')
    {
        $rDetail = '';

        if ($route == 'ราคาบอล') {
            $rDetail = 'ffp';
        // } else if ($route == 'dir-list') {
        //     $rDetail = 'dir-list';
        } else if ($route == 'content-detail/{link}') {
            $rDetail = 'content-detail';
        }

        return $rDetail;
    }

    public function allColumn(Request $request)
    {
        $table = $request->table_name;
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        return response()->json($columns);
    }

    public function dataTableDetail(Request $request)
    {
        $ret_data = array();
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start');
        $length = (int) $request->input('length');
        $order = $request->input('order');
        $tableName = $request->input('table_name');
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        // $searchText = $request->input('search');
        // $searchText = trim($searchText);

        // ------------- start total --------------- //
        // DB::enableQueryLog();
        $tableData = DB::table($tableName);
            // ->select('users.username', 'articles.id as atc_id', 'articles.title', 'articles.media_id', 'articles.created_at', 'articles.article_status', 'articles.active_status'); // as username
        $dts = $tableData;

        // if ($searchText) {
        //     $dts = $tableData->where('title', 'like', $searchText . '%');
        // }

        $recordsTotal = $dts->count();
        // $q = DB::getQueryLog()[0]['query'];
        // dd($q);
        // ------------- end total --------------- //

        // ------------- start datas --------------- //
        // DB::enableQueryLog();
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        $orderByColumn = $columns[0];
        $mnData = DB::table($tableName)->orderBy($orderByColumn, 'desc');
            // ->join('users', 'users.id', '=', 'articles.user_id')
            // ->select('users.username', 'articles.id as atc_id', 'articles.title', 'articles.media_id', 'articles.created_at', 'articles.article_status', 'articles.active_status'); // as username

        // if (trim($searchText)) {
        //     $datas = $mnData->where('title', 'like', $searchText . '%');
        // }

        // if (array_key_exists('column', $order[0]) && array_key_exists('dir', $order[0])) {
        //     $datas = $mnData->orderBy($this->order_by[$order[0]['column']], $order[0]['dir']);
        // }

        $datas = $mnData->skip((int) $start)->take($length)->get();
        $total = count($datas);

        // $q = DB::getQueryLog()[0]['query'];
        // dd($q);
        // dd(DB::getQueryLog()[0]['time']);
        // ------------- end datas --------------- //

        $protocol = 'http://'; // (env('APP_ENV') === 'production') ? 'https://' : 'http://';
        $host = $request->getHttpHost();
        $url = $protocol . $host;

        if ($total > 0) {
            foreach ($datas as $data) {
                $rowData = (array) $data;
                $row = array();
                $href = '่#';

                if ($tableName == 'ffp_detail') {
                    $dirName = $rowData['dir_name'];
                    $linkCode = $dirName . '-' . $rowData['file_name'];
                    $href = $url . '/content-detail?link=' . $linkCode;
                }

                foreach($columns as $col) {
                    if ($col == 'content') {
                        $row[] = 'Length: ' . strlen($rowData[$col]);
                    } else if ($col == 'link') {
                        $row[] = '<div class="col-link">' . $rowData[$col] . '</div>';
                    } else {
                        $row[] = $rowData[$col];
                    }
                }

                if ($tableName == 'ffp_detail') {
                    $row[] = '<a href="' . $href . '" target="_BLANK">Info</a>';
                } else {
                    $row[] = '...';
                }

                $ret_data[] = $row;
            }

            $datas = ["draw" => ($draw) ? $draw : 0,
                "recordsTotal" => (int) $recordsTotal,
                "recordsFiltered" => (int) $recordsTotal,
                "data" => $ret_data];

            echo json_encode($datas);
        } else {
            $datas = ["draw" => ($draw) ? $draw : 0,
                "recordsTotal" => (int) $recordsTotal,
                "recordsFiltered" => (int) $recordsTotal,
                "data" => $ret_data];

            echo json_encode($datas);
        }
    }
}
