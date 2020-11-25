<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\SystemInfoController as SystemInfo;
use App\Http\Controllers\API\FileController as FileAPI;
// use App\Http\Controllers\API\ContentDetailController as CTDetail;
use App\Models\DirList;
use App\Models\ContentDetail;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    private $systemInfo;
    private $fileApi;

    public function __construct()
    {
        $this->systemInfo = new SystemInfo();
        $this->fileApi = new FileAPI();
        // $this->contentDetail = new CTDetail();
    }

    public function index()
    {
        // $this->contentDetail->currentContent();
        $datas = array('routes' => array());
        return view('welcome', $datas);
    }

    public function routeList()
    {
        $routeDatas = $this->systemInfo->routeList();

        $datas = array('routes' => $routeDatas);
        return view('welcome', $datas);
    }

    public function tableList()
    {
        $tableList = $this->systemInfo->tableList();

        $datas = array('tables' => $tableList);
        return view('table-list', $datas);
    }

    public function dataTableDetail(Request $request)
    {
        $tableName = $request->table_name;
        $datas = array('table_name' => $tableName);
        return view('data-table-detail', $datas);
    }

    public function logHtml()
    {
        $logDatas = file_get_contents('../../node-scraper/log.html');
        $datas = array('log_content' => $logDatas);
        return view('log-html', $datas);
    }

    public function logsHtml()
    {
        $logOneDatas = file_get_contents('../storage/app/log1.html');
        $logTwoDatas = file_get_contents('../storage/app/log2.html');
        $datas = array('log_one' => $logOneDatas, 'log_two' => $logTwoDatas);
        return view('logs-html', $datas);
    }

    public function logLaravel()
    {
        $logDatas = file_get_contents('../storage/logs/laravel.log');
        $datas = array('log_content' => $logDatas);
        return view('log-laravel', $datas);
    }

    public function logos()
    {
        $logoData = $this->fileApi->logos();
        return $logoData;
    }

    // --- start provide DB --- //
    public function checkScrapingStatus(Request $request)
    {
        $latestDir = $request->latest_dir;
        $chkStatusList = array();

        // --- start latest 10 files --- //
        $updatedAtData = DirList::select('created_at')->where('dir_name', $latestDir);
        if ($updatedAtData->count() > 0) {
            $row = $updatedAtData->get();
            $updatedAt = $row[0]->created_at;

            if ($updatedAt) {
                $chkDatas = DirList::select('dir_name', 'scraping_status');
                $chkDatas->where('created_at', '<=', $updatedAt);
                $chkDatas->orderBy('dir_name', 'desc');

                if ($chkDatas->count() > 0) {
                    $rows = $chkDatas->take(20)->get();

                    foreach($rows as $row) {
                        $dirName = $row->dir_name;
                        $scrapingStatus = $row->scraping_status;
                        $chkStatusList[] = array('dir_name' => $dirName, 'scraping_status' => $scrapingStatus);
                    }
                }
            }
        }
        // --- end latest 10 files --- //

        return response()->json($chkStatusList);
    }

    public function mainPrice(Request $request)
    {
        $latestDir = $request->latest_dir;
        $dirList = array();

        $datas = DirList::select('dir_name', 'content', 'scraping_status', 'created_at');

        if ($latestDir) {
            $datas->where('dir_name', '>', $latestDir);
        }
        
        $datas->orderBy('dir_name', 'asc');

        if ($datas->count() > 0) {
            $dirList = $datas->skip(0)->take(3)->get();
        }

        return response()->json($dirList);
    }

    public function graphDetail(Request $request)
    {
        $latestId = $request->latest_id;
        $graphList = array();

        $datas = ContentDetail::select('id', 'code', 'link', 'dir_name', 'file_name', 'league_name', 'vs', 'event_time', 'content', 'created_at');
    
        if ($latestId) {
            $datas->where('id', '>', $latestId);
        }

        $datas->orderBy('id', 'asc');

        $detailList = array();

        if ($datas->count() > 0) {
            $graphList = $datas->skip(0)->take(10)->get();

            foreach($graphList as $data) {
                $mainDatas = DirList::select('scraping_status')->where('dir_name', $data->dir_name)->first();

                if ($mainDatas) {
                    $scpStt = (int) $mainDatas->scraping_status;

                    if (($data->content == null || $data->content == '') && $scpStt == 1) {
                        DB::table('ffp_detail')->where('id', $data->id)->delete();
                    } else {
                        $detailList[] = $data;
                    }
                }
            }
        }

        return response()->json($detailList);
    }

    public function checkDetailDatas(Request $request)
    {
        $ids = $request->ids;
        $idsDatas = array();

        $idsList = explode(',', $ids);

        $chkDatas = ContentDetail::select('id', 'dir_name', 'content', 'league_name', 'vs', 'event_time')->whereIn('id', $idsList);

        if ($chkDatas->count() > 0) {
            $rows = $chkDatas->get();

            foreach($rows as $data) {
                $ffpList = dirList::where('dir_name', $data->dir_name)->select('scraping_status');

                $forceDelete = 0;

                if ($ffpList->count() > 0) {
                    $dirs = $ffpList->get();
                    $dirData = $dirs[0];

                    if ((int) $dirData->scraping_status == 1 && ($data->content == null || $data->content == '')) {
                        $forceDelete = 1;
                    }
                }

                $idsDatas[] = array(
                    'id' => $data->id,
                    'league_name' => $data->league_name,
                    'vs' => $data->vs,
                    'event_time' => $data->event_time,
                    'content' => $data->content,
                    'force_delete' => $forceDelete
                );
            }
        }

        return response()->json($idsDatas);
    }

    public function checkLeagueDatas(Request $request)
    {
        $ids = $request->ids;
        $idsDatas = array();

        $idsList = explode(',', $ids);

        $chkDatas = ContentDetail::select('id')->whereIn('id', $idsList);

        if ($chkDatas->count() > 0) {
            $rows = $chkDatas->get();

            foreach($rows as $data) {
                $ffpDetail = ContentDetail::find($data->id);
                if ($ffpDetail) {
                    $idsDatas[] = array(
                        'id' => $ffpDetail->id,
                        'league_name' => $ffpDetail->league_name,
                        'vs' => $ffpDetail->vs,
                        'event_time' => $ffpDetail->event_time
                    );
                }
            }
        }

        return response()->json($idsDatas);
    }
    // --- end provide DB --- //
}
