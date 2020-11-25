<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DBProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // --- start provider --- //
    public function mainPrice(Request $request)
    {
        $ip = $request->ip;
        $dirNameList = $request->dir_names;
        // client sent exist id list
        // api query left data and set 10 rows/time

        $mnData = DB::table('ffp_list')
            ->select('dir_name', 'content', 'scraping_status', 'created_at')
            ->whereNotIn('dir_name', $dirNameList)
            ->orderBy('created_at', 'asc'); // add updated_at in client

        $datas = $mnData->skip(0)->take(3)->get();

        // return last 5 files of dirNameList => scraping_status

        return response()->json($datas);
    }

    public function graphDetail(Request $request)
    {
        $ip = $request->ip;
        $idList = $request->ids;
        // client sent exist id list
        // api query left data and set 10 rows/time

        $mnData = DB::table('ffp_detail')
            ->select('id', 'code', 'link', 'dir_name', 'file_name', 'league_name', 'vs', 'event_time', 'content', 'created_at')
            ->whereNotIn('id', $dirNameList)
            ->orderBy('id', 'asc'); // add updated_at in client

        $datas = $mnData->skip(0)->take(10)->get();

        return response()->json($datas);
    }
    // --- end provider --- //
}
