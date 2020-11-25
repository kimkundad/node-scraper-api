<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ContentDetailController as CTDetail;
use App\Models\ContentDetail;
use App\Models\DirList;

class ContentDetailController extends Controller
{
    private $contentDetail;

    public function __construct()
    {
        $this->contentDetail = new CTDetail();

        /*
        $conn = new CheckConnectionController();
        $this->connDatas = $conn->checkConnServer();

        if ($this->connDatas['connected']) {
            $table = $conn->checkTableExist('generals');

            if ($table['table']) {
                $this->welcome = new WelcomeAPI();
                $this->article = new ArticleAPI();
                $this->widget = new WidgetAPI();
                $this->page = new PageAPI();
            } else {
                abort(500, 'Not found table: generals.');
            }
        } else {
            abort(500, 'Cannot connect database.');
        }
        */
    }

    public function index()
    {
        return view('current-content');
    }

    public function inner(Request $request)
    {
        $link = $request->link;
        $datas = array('link' => $link);
        return view('inner-content', $datas);
    }

    public function prediction()
    {
        // $datas = $this->contentDetail->genLinkCode();
        // return response()->json($datas);

        // $datas = $this->contentDetail->prediction();
        // dd($datas);

        return view('prediction');
    }

    public function contentDetail(Request $request)
    {
        $dirName = '';
        $link = $request->link;

        $dirName = substr($link, 0, 13);

        $datas = array('link' => $link, 'dir_name' => $dirName);
        return view('content-detail', $datas);
    }
}
