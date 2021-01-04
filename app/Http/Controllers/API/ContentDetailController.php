<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\CommonController;
use Illuminate\Http\Request;
use App\Models\ContentDetail;
use App\Models\DirList;
use App\Models\FileDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Storage;
// use \stdClass;

class ContentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $common;
    public $attrPatterns;
    public $attrWithLinkPatterns;
    public $textInClasspatterns;
    public $textInClassReplaces;
    public $removeLink;
    public $rplBySharp;
    public $rplByScript;

    public function __construct()
    {
        $this->common = new CommonController();
        $this->attrPatterns = '#\s(id|title|onmouseover|onmouseout|border|cellpadding|cellspacing)="[^"]+"#';
        $this->attrWithLinkPatterns = '#\s(id|title|href|onmouseover|onmouseout|border|cellpadding|cellspacing)="[^"]+"#';
        $this->textInClasspatterns = array('/ Open/', '/ Closed/', '/<span class>/');
        $this->textInClassReplaces = array('', '', '/<span>/');
        $this->removeLink = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
        $this->rplBySharp = '#';
        $this->rplByScript = 'javascript:void(0);';

        ini_set('max_execution_time', 300);
        set_time_limit(300);
    }

    /*
    public function index()
    {
        $datas = $this->currentContent();
        return response()->json($datas);
    }*/

    public function testFFPStructure()
    {
        $datas = DirList::select('content')->orderBy('dir_name', 'desc');
        $structureList = array();

        if ($datas->count() > 0) {
            $datas = $datas->get();
            $latestContent = $datas[0]->content;

            
            $contentList = json_decode($latestContent);

            if (count($contentList) > 0) {
                foreach($contentList as $data) {
                    $topHead = $data->top_head;
                    $leagueList = $data->datas;

                    $newLeagueList = array();

                    if (count($leagueList) > 0) {
                        foreach($leagueList as $league) {
                            $lName = $league->league_name;
                            $matchDatas = $league->match_datas;

                            $leftTeamGroup = array();
                            $matchList = array();

                            if (count($matchDatas) > 0) {
                                foreach($matchDatas as $match) {
                                    $leftTeam = $match->left[0];
                                    $rightTeam = $match->right[0];
                                    $nameCheck = $leftTeam . '_' . $rightTeam;
                                    if (! in_array($nameCheck, $leftTeamGroup)) {
                                        $leftTeamGroup[] = $nameCheck;
                                        $matchList[] = (array) $match;
                                    }
                                }

                                foreach($matchList as $k => $match) {
                                    $leftTeam = $match['left'][0];
                                    $rightTeam = $match['right'][0];
                                    $nameCheck = $leftTeam . '_' . $rightTeam;
                                    foreach($matchDatas as $mData) {
                                        $lTeam = $mData->left[0];
                                        $rTeam = $mData->right[0];
                                        $nCheck = $lTeam . '_' . $rTeam;
                                        if ($nameCheck == $nCheck) {
                                            $matchList[$k]['left_list'][] = $mData->left;
                                            $matchList[$k]['right_list'][] = $mData->right;
                                        }
                                    }
                                }
                            }

                            $newLeagueList[] = array(
                                'league_name' => $lName,
                                'match_datas' => $matchList
                            );
                        }
                    }

                    $structureList[] = array('top_head' => $topHead, 'datas' => $newLeagueList);
                }
            }
        }

        return response()->json($structureList); 
    }

    public function index()
    {
        $curDatas = $this->realCurrentContent();
        $found = $curDatas['found'];
        $dirName = $curDatas['dirName'];
        $latestContent = $curDatas['latestContent'];

        $datas = array();
        $structureList = array();
        
        if ($found == 1) {
            $contentList = json_decode($latestContent);
            
            if (count($contentList) > 0) {
                foreach($contentList as $data) {
                    
                    $topHead = $data->top_head;
                    $leagueList = $data->datas;
                    
                    $newLeagueList = array();

                    if (count($leagueList) > 0) {
                        foreach($leagueList as $league) {

                            $lName = $league->league_name;
                            $matchDatas = $league->match_datas;
                            
                            $leftTeamGroup = array();
                            $matchList = array();

                            if (count($matchDatas) > 0) {
                                foreach($matchDatas as $match) {
                                    $leftTeam = $match->left[0];
                                    $rightTeam = $match->right[0];
                                    $nameCheck = $leftTeam . '_' . $rightTeam;
                                    if (! in_array($nameCheck, $leftTeamGroup)) {
                                        $leftTeamGroup[] = $nameCheck;
                                        $matchList[] = (array) $match;
                                    }
                                }

                                foreach($matchList as $k => $match) {
                                    $leftTeam = $match['left'][0];
                                    $rightTeam = $match['right'][0];
                                    $nameCheck = $leftTeam . '_' . $rightTeam;
                                    foreach($matchDatas as $mData) {
                                        $lTeam = $mData->left[0];
                                        $rTeam = $mData->right[0];
                                        $nCheck = $lTeam . '_' . $rTeam;
                                        if ($nameCheck == $nCheck) {
                                            $matchList[$k]['left_list'][] = $mData->left;
                                            $matchList[$k]['right_list'][] = $mData->right;
                                        }
                                    }

                                    $matchList[$k]['detail_id'] = $this->detailIdFromLink($match['link'], $dirName);
                                }
                            }
                            
                            $newLeagueList[] = array(
                                'league_name' => $lName,
                                'match_datas' => $matchList
                            );
                        }
                    }

                    $structureList[] = array('top_head' => $topHead, 'datas' => $newLeagueList);
                    
                }
            }

            /*
            if (count($contentList) > 0 && count($links) > 0) {
                foreach($links as $link) {
                    $matchDatas = array();
                    foreach($contentList as $data) {
                        $topHead = $data->top_head;
                        $matchList = $data->datas;

                        if (count($matchList) > 0) {
                            foreach($matchList as $raw) {
                                if ($raw->link == $link) {
                                    $matchDatas[] = array('top_head' => $topHead, 'matches' => $raw);
                                }
                            }
                        }
                    }

                    $detailId = '';
                    $detailDatas = ContentDetail::select('id')->where('dir_name', $dirName)->where('link', $link);

                    if ($detailDatas->count() > 0) {
                        $rows = $detailDatas->get();
                        $detailId = $rows[0]->id;
                    }

                    $datas[] = array('link' => $detailId, 'match_datas' => $matchDatas);
                }
            }*/
        }

        $domain = request()->getHttpHost();
        $mainDatas = array('raw_group' => $structureList, 'latest_dir' => $dirName, 'domain' => $domain);
        //dd($mainDatas);
        return response()->json($mainDatas);
    }

    public function detailIdFromLink($link = '', $dirName = '')
    {
        $detailId = '';
        $dtDatas = ContentDetail::select('id')->where('link', $link)->where('dir_name', $dirName);

        if ($dtDatas->count() > 0) {
            $rows = $dtDatas->get();
            $detailId = $rows[0]->id;
        }

        return $detailId;
    }

    public function currentContent()
    {
        $curDatas = $this->realCurrentContent();
        $found = $curDatas['found'];
        $dirName = $curDatas['dirName'];
        $latestContent = $curDatas['latestContent'];

        $datas = array();

        if ($found == 1) {
            $rawGroupFromContent = $this->rawGroupContent($latestContent, $dirName);
            // $ffpContent = $rawGroupFromContent['latest_content'];
            $rawGroupDatas = $rawGroupFromContent['raw_group'];
            // $theLegend = $rawGroupFromContent['the_legend'];
    
            $domain = request()->getHttpHost();
    
            // , 'latest_content' => $ffpContent
            // , 'the_legend' => $theLegend
            $datas = array('latest_dir' => $dirName, 'raw_group' => $rawGroupDatas, 'domain' => $domain);
        }

        return $datas; // return current content
    }

    public function realCurrentContent()
    {
        $dirName = '';
        $latestContent = '';
        $found = 0;

        $latestDir = DirList::select(['dir_name', 'content'])->where('scraping_status', '0')->where('content', '<>', '[]')->orderBy('dir_name', 'desc')->take(1);
        
        if ($latestDir->count() > 0) {
            $latestDatas = $latestDir->get();
            $found = 0;
            
            foreach($latestDatas as $data) {
                
                if ($found == 0) {
                    $dirName = $data->dir_name;
                    $detailDatas = ContentDetail::select('id')->where('dir_name', $dirName)->orderBy('code', 'asc');
                    
                    if ($detailDatas->count() > 0) {
                        $found = 1;
                        $latestContent = $data->content;
                    }
                }
            }
        }

        return array('found' => $found, 'dirName' => $dirName, 'latestContent' => $latestContent);
    }

    public function rawGroupContent($latestDirContent, $dirName)
    {
        $ffpContent = array();
        $rawGroupDatas = array();
        $theLegend = array();

        if (trim($latestDirContent) && $latestDirContent != null) {
            $ffpContent = $this->arrangeFFPContent($latestDirContent, $dirName);
            $raws = $ffpContent['raws'];
            $links = $ffpContent['links'];
            // $theLegend = $ffpContent['the_legend'];
            if (count($raws) > 0 && count($links) > 0) {
                foreach($links as $link) {
                    $matchDatas = array();
                    foreach($raws as $raw) {
                        if ($raw['link'] == $link) {
                            $matchDatas[] = $raw;
                        }
                    }
                    $rawGroupDatas[] = array('link' => $link, 'match_datas' => $matchDatas);
                }
            }
        }

        // 'latest_content' => $ffpContent,
        // , 'the_legend' => $theLegend
        return array('raw_group' => $rawGroupDatas);
    }

    public function arrangeFFPContent($htmlContent = '', $dirName = '')
    {
        $theLegend = array();

        $result = preg_replace($this->attrPatterns, '', $htmlContent);
        $result = preg_replace($this->textInClasspatterns, $this->textInClassReplaces, $result);
        // $result = preg_replace($removeLink, '#', $result);

        $result = preg_replace_callback('~"[^"]*"~', function ($m) {
            return preg_replace('~\s~', '', $m[0]);
        }, $result); // replace space between " "

        $divContent = explode('<div class="NonLiveMarket">', $result);

        // $marketContent = (array_key_exists(0, $divContent)) ? $divContent[0] : '';
        $nonMarketContent = (array_key_exists(1, $divContent)) ? $divContent[1] : '';

        // $liveMarket = $marketContent;
        $nonLiveMarket = (trim($nonMarketContent)) ? '<div class="NonLiveMarket">' . $nonMarketContent : '';

        $MarketT = explode('class="MarketT">', $nonLiveMarket);
        array_shift($MarketT);
        // echo count($MarketT);

        $links = array();
        $rawMatchList = array();

        if (count($MarketT) > 0) {
            foreach ($MarketT as $k => $mk) {
                $headBd = explode('class="MarketBd">', $mk);
                $marketHd = $headBd[0];
                preg_match("'<span>(.*?)</span>'si", $marketHd, $raws);
                $topHead = (array_key_exists(1, $raws)) ? $raws[1] : '';

                $marketBd = $headBd[1];
                $leagueList = explode('class="MarketLea">', $marketBd);
                array_shift($leagueList);

                $lList = array();

                if (count($leagueList) > 0) {
                    foreach ($leagueList as $key => $value) {
                        preg_match('/<div class="SubHeadT">(.*?)<\/div>/s', $value, $rws);
                        $subHeadT = (array_key_exists(1, $rws)) ? $rws[1] : '';

                        preg_match("'<tbody>(.*?)</tbody>'si", $value, $trRaws);
                        $trContent = (array_key_exists(1, $trRaws)) ? $trRaws[1] : '';

                        $trList = explode('<tr>', $trContent);

                        array_shift($trList);

                        $matches = array();

                        if (count($trList) > 0) {
                            $matchlList = array();

                            foreach ($trList as $k => $v) {
                                preg_match('/<div class="DateTimeTxt">(.*?)<\/div>/s', $v, $ctRws);
                                $dateTime = (array_key_exists(1, $ctRws)) ? $ctRws[1] : '';

                                $arrStr = explode('<br>', $dateTime);
                                $match_result = preg_replace('/\//', '', strip_tags($arrStr[0]));
                                $date_time = preg_replace('/\//', '', strip_tags($arrStr[1]));

                                $oddsTabL = explode('OddsTabL', $v);
                                array_shift($oddsTabL);
                                $leftTeam = $oddsTabL[0];
                                preg_match('/<span class="OddsL">(.*?)<\/span>/s', $leftTeam, $leftRws);
                                $leftTeamName = (array_key_exists(1, $leftRws)) ? $leftRws[1] : '';
                                preg_match('/<span class="OddsM">(.*?)<\/span>/s', $leftTeam, $leftRws);
                                $leftTeamScore = (array_key_exists(1, $leftRws)) ? $leftRws[1] : '';
                                preg_match('/<span class="OddsR">(.*?)<\/span>/s', $leftTeam, $leftRws);
                                $leftLastNum = (array_key_exists(1, $leftRws)) ? $leftRws[1] : '';

                                $rightTeam = (array_key_exists(1, $oddsTabL)) ? $oddsTabL[1] : '';

                                $drawText = '';
                                $drawScore = '';
                                $rightTeamName = '';
                                $rightTeamScore = '';
                                $rightLastNum = '';

                                if (strlen(trim($rightTeam)) == 0) {
                                    $oddsTabL = explode('</td>', $leftTeam);
                                    $rightData = (array_key_exists(1, $oddsTabL)) ? $oddsTabL[1] : '';

                                    preg_match('/<span class="OddsM">(.*?)<\/span>/s', $rightData, $lNum);
                                    $rightTeamScore = (array_key_exists(1, $lNum)) ? $lNum[1] : '';
                                    preg_match('/<span class="OddsR">(.*?)<\/span>/s', $rightData, $lNumPlus);
                                    $rightLastNum = (array_key_exists(1, $lNumPlus)) ? ' ' . $lNumPlus[1] : '';
                                    preg_match('/<span class="OddsL">(.*?)<\/span>/s', $rightData, $rtName);
                                    $rightTeamName = (array_key_exists(1, $rtName)) ? ' ' . $rtName[1] : '';
                                } else {
                                    $oddsTabR = explode('OddsTabR', $rightTeam);
                                    $rightTeamDraw = $oddsTabR[0];
                                    $rightTeamDrawText = preg_match('/<span class="OddsL">(.*?)<\/span>/s', $rightTeamDraw, $rtdtRws);
                                    $drawText = (array_key_exists(1, $rtdtRws)) ? $rtdtRws[1] : '';
                                    $rightTeamDrawScore = preg_match('/<span class="OddsR">(.*?)<\/span>/s', $rightTeamDraw, $rtdsRws);
                                    $drawScore = (array_key_exists(1, $rtdsRws)) ? $rtdsRws[1] : '';

                                    $rightTeamTeam = (array_key_exists(1, $oddsTabR)) ? $oddsTabR[1] : '';
                                    $rightTeamTeamName = preg_match('/<span class="OddsL">(.*?)<\/span>/s', $rightTeamTeam, $rttnRws);
                                    $rightTeamName = (array_key_exists(1, $rttnRws)) ? $rttnRws[1] : '';
                                    preg_match('/<span class="OddsR">(.*?)<\/span>/s', $rightTeamTeam, $rttsRws);
                                    $rightLastNum = (array_key_exists(1, $rttsRws)) ? $rttsRws[1] : '';
                                }

                                preg_match('/<td class="Icons">(.*?)<\/td>/s', $v, $numAction);
                                $someFullNum = (array_key_exists(1, $numAction)) ? $numAction[1] : '';

                                $linkExpl = explode('<div', $someFullNum);
                                $linkFirst = $linkExpl[0];
                                $linkFirst = preg_replace('#\s(id|title|onmouseover|onmouseout|class)="[^"]+"#', '', $linkFirst);

                                preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $linkFirst, $linkMatches);
                                $lastLink = (array_key_exists(2, $linkMatches)) ? $linkMatches[2] : array();
                                $link = (array_key_exists(0, $lastLink)) ? $lastLink[0] : '';
                                // $link = ($link) ? '<a href="http://www.beer789.com' . $link . '" target="_BLANK">ดูราคาบอลไหล</a>' : '';

                                // $regex_no_href = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
                                // $someNum = preg_replace($regex_no_href, '#', $someFullNum);
                                // preg_match('/<a href="#">(.*?)<\/a>/s', $someNum, $numbs);
                                // $actionNum = (array_key_exists(1, $numbs)) ? $numbs[1] : '';

                                $linkCode = '';

                                $linkCodeDatas = ContentDetail::select('id')->where('dir_name', $dirName)->where('link', $link);
                                if ($linkCodeDatas->count() > 0) {
                                    $lc = $linkCodeDatas->get();
                                    $linkCode = $lc[0]->id;
                                    // $linkCode = $dirName . '-' . $lc[0]->file_name;

                                    if (!in_array($linkCode, $links)) {
                                        $links[] = $linkCode;
                                    }
                                }

                                $timestamp = $this->timeMinusOneHr(trim($date_time));

                                $tm = Date('H:i', $timestamp);

                                $league_row = array('league_name' => $subHeadT,
                                    'match_result' => trim($match_result),
                                    'date_time' => trim($date_time),
                                    'date_time_before' => $tm, // -1 hr
                                    'left_team_name' => $leftTeamName,
                                    'left_team_score' => $leftTeamScore,
                                    'left_last_num' => $leftLastNum,
                                    'draw_text' => $drawText,
                                    'draw_score' => $drawScore,
                                    'right_team_name' => $rightTeamName,
                                    'right_team_score' => $rightTeamScore,
                                    'right_last_num' => $rightLastNum,
                                    'link' => $linkCode);

                                $matchlList[] = array('name' => $subHeadT, 'league_row' => $league_row);
                                $rawMatchList[] = $league_row;
                            }
                            // group league name

                            $matches = $this->groupMatchlList($matchlList);

                        }

                        $lList[] = $matches;
                    }
                }

                $theLegend[] = array('top_head' => $topHead, 'league_list' => $lList);
            }
        }

        // 'the_legend' => $theLegend, 
        return array('links' => $links, 'raws' => $rawMatchList);
    }

    public function timeMinusOneHr($date_time)
    {
        $dateTime = Date('Y-m-d') . ' ' . $date_time .':00';
        $dateTimeBefore = strtotime($dateTime . ' -1 hours');
        return $dateTimeBefore;
    }

    public function groupMatchlList($matchlList = array())
    {
        $matches = array();

        if (count($matchlList) > 0) {
            $names = array();
            foreach ($matchlList as $element) {
                if (! in_array($element['name'], $names)) {
                    $names[] = $element['name'];
                }
            }

            foreach ($names as $name) {
                $league_row = array();
                foreach ($matchlList as $ele) {
                    if ($ele['name'] == $name) {
                        $league_row[] = $ele['league_row'];
                    }
                }

                $matches[] = array('name' => $name, 'league_row' => $league_row);
            }

        }

        return $matches;
    }

    public function dataToGraph(Request $request)
    {
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        // header("Content-Type: text/plain");
        // date_default_timezone_set("Asia/Bangkok");

        $detailId = $request->detail_id;
        $datas = $this->dataToGraphWithDetailId($detailId);

        return response()->json($datas);
    }

    public function dataToGraphWithDetailId($detailId = 0)
    {
        $graphDatas = $this->prepareDataToPlotGraph($detailId);

        $asianDatas = $this->graphLastArrange($graphDatas, 'asian');
        $overDatas = $this->graphLastArrange($graphDatas, 'over');
        $oneDatas = $this->graphLastArrange($graphDatas, 'one');

        $datas = array('asian' => $asianDatas,
                        'over' => $overDatas,
                        'one' => $oneDatas);

        return $datas;
    }

    public function graphLastArrange($graphDatas, $mode)
    {
        $timeList = array();
        $chkTeamNames = array();
        $teamSeries = array();
        // $teams = array();

        if (count($graphDatas) > 0) {
            foreach($graphDatas as $key => $graph) {
                $value = $graph[$mode];

                if (array_key_exists('date_time', $value)) {
                    $dTime = $value['date_time'];
                    if ($dTime) {
                        $timeList[] = $dTime;

                        if (count($value['matches']) > 0) {
                            foreach($value['matches'] as $val) {
                                $tempName = ($mode == 'one') ? $val['team_name'] : $val['team_name'] . ':' . $val['score'];
                                // if (! in_array($val['team_name'], $teams)) {
                                //     $teams[] = $val['team_name'];
                                // }
                                if (! in_array($tempName, $chkTeamNames)) {
                                    $chkTeamNames[] = $tempName;
                                    $teamSeries[] = array('name' => $tempName, 'data' => array());
                                }
                            }
                        }
                    }
                }
            }

            if (count($teamSeries) > 0) {
                foreach($teamSeries as $n => $team) {
                    $datas = array();
                    foreach($timeList as $tk => $time) {
                        $score = -999;
                        $water = -999;
                        foreach($graphDatas as $graph) {
                            $asian = $graph[$mode];
                            if (array_key_exists('matches', $asian)) {
                                foreach($asian['matches'] as $match) {
                                    $wdTeam = ($mode == 'one') ? $match['team_name'] : $match['team_name'] . ':' . $match['score'];
                                    if (($team['name'] == $wdTeam) && ($asian['date_time'] == $time)) {
                                        $score = $match['score'];
                                        $water = $match['water'];
                                    }
                                }
                            }
                        }

                        $score = ($score == -999) ? null : $score;
                        $water = ($water == -999) ? null : $water;
                        $datas[] = $water;
                    }

                    $teamSeries[$n]['data'] = $datas;
                }
            }
        }

        $timeKeyList = array();
        $timeKeyData = array();
        if (count($timeList) > 0 && count($teamSeries) > 0) {
            foreach($timeList as $key => $time) {
                $noTime = 0;
                foreach($teamSeries as $teamData) {
                    $data = $teamData['data'];
                    foreach($data as $k => $tm) {
                        if ($k == $key) {
                            if (!$tm) {
                                $noTime++;
                            }
                        }
                    }
                }

                if ($noTime == count($teamSeries)) {
                    if (! in_array($time, $timeKeyList)) {
                        $timeKeyList[] = $time;
                        $timeKeyData[] = array('k' => $key, 'v' => $time);
                    }
                }
            }

            if (count($timeKeyData) > 0) {
                foreach($timeList as $tm) {
                    foreach($timeKeyData as $tData) {
                        $rmKey = $tData['k'];
                        $rmVal = $tData['v'];
                        if ($rmVal == $tm) {
                            unset($timeList[$rmKey]);
                        }
                    }
                }

                foreach($teamSeries as $idx => $teamData) {
                    $data = $teamData['data'];
                    $newData = array();
                    foreach($data as $k => $tm) {
                        foreach($timeKeyData as $tData) {
                            $rmKey = $tData['k'];
                            if ($rmKey == $k) {
                                unset($teamSeries[$idx]['data'][$k]);
                            }
                        }
                    }
                }
            }
        }

        if (count($teamSeries) > 0) {
            foreach($teamSeries as $index => $tmData) {
                $data = $tmData['data'];
                $newData = array();
                if (count($data) > 0) {
                    foreach($data as $ntm) {
                        $newData[] = $ntm;
                    }
                }

                $teamSeries[$index]['data'] = $newData;
            }
        }

        $newTimeList = array();
        // $oldTimeList = array();
        if (count($timeList) > 0) {
            foreach($timeList as $time) {
                // $oldTimeList[] = $time;

                $dbFormat = substr($time, 0, 4) . '-' . substr($time, 4, 2) . '-' . substr($time, 6, 2);
                $timeInfo = substr($time, 9, 2) . ':' . substr($time, 11, 2); // 20200409-1856
                $newTimeList[] = $this->common->showDateMonth($dbFormat , 2) . ' ' . $timeInfo;
            }
        }

        // $min = $this->findMin($teamSeries);

        $name = ($mode == 'asian') ? 'Asian Handicap' : '';
        $name = ($mode == 'over') ? 'Over Under' : $name;
        $name = ($mode == 'one') ? '1X2' : $name;

        // , 'old_time_list' => $oldTimeList, 'teams' => $teams
        $datas = array('name' => $name, 'time_list' => $newTimeList, 'team_series' => $teamSeries);

        return $datas;
    }

    public function prepareDataToPlotGraph($id = '')
    {
        $graphDatas = array();

        $findDatas = ContentDetail::select(['link'])->where('id', $id);

        if ($findDatas->count() > 0) {
            $rows = $findDatas->get();
            $realLink = $rows[0]->link;

            // Storage::disk('local')->put('log.html', $realLink);
            
            $dayList = ContentDetail::select('dir_name')->groupBy('dir_name')->orderBy('dir_name', 'asc');
            $totalInner = $dayList->count();
            $successList = array();
            if ($totalInner > 0) {
                $dirList = $dayList->get();
                foreach($dirList as $key => $dName) {
                    $dlDatas = dirList::select('dir_name')->where('scraping_status', '1')->where('dir_name', $dName->dir_name);
                    if ($dlDatas->count() > 0) {
                        $successList[] = $dName->dir_name;
                    }
                }
            }
        }

        if (count($successList) > 0) {
            $contentDatas = ContentDetail::select(['content', 'dir_name'])->where('link', $realLink)->whereNotNull('content');
            $contentDatas->whereIn('dir_name', $successList);
            $contentDatas->orderBy('file_name', 'asc');

            // $message = 'dir_name: ' . $day->dir_name . ', Total: ' . $contentDatas->count();
            // Log::info($message);

            if ($contentDatas->count() > 0) {
                foreach($contentDatas->get() as $val) {
                    $htmlContent = $val->content;
                    $dir_name = $val->dir_name;

                    $graphDatas[] = $this->arrangeGraphDatas($dir_name, $htmlContent);
                }
            }
        }

        // return array('prepare_data' => $graphDatas, 'content' => $htmlContent);
        return $graphDatas;
    }

    public function arrangeGraphDatas($currentDir = '', $htmlContent = '')
    {
        $asianHandicap = array();
        $overUnder = array();
        $onePlusTwo = array();
        $countAsian = 0;
        $countOver = 0;
        $countOne = 0;

        if ($htmlContent) {
            // Storage::disk('local')->put('log.json', $htmlContent);
            $tableDatas = json_decode($htmlContent);
    
            if (count($tableDatas) > 0) {
                foreach($tableDatas as $innerContent) {
                    // $allVariables = $this->skudYen($innerContent, $topHead);
                    // Storage::disk('local')->put('log.json', json_encode($innerContent));
                    $topHead = $innerContent->top_head;
                    // Storage::disk('local')->put('log.html', $topHead);
                    if ($innerContent->datas) {
                        $matches = array();
                        if (count($innerContent->datas) > 0) {
                            foreach($innerContent->datas as $data) {
                                if ($data) {
                                    $row = (array) $data;
                                    Storage::disk('local')->put('log1.html', gettype($row) . '<br>' . json_encode($row));
                                    // Storage::disk('local')->put('log2.html', gettype($data));

                                    $teamLeftName = '';
                                    $teamLeftRight = null;
                                    $teamRightName = '';
                                    $teamRightRight = null;
                                    // Storage::disk('local')->put('log.html', $teamLeftName);

                                    if ($topHead == '1X2') {
                                        $teamDrawText = 'Draw';
                                        $teamDrawScore = null;

                                        if (array_key_exists('left', $row)) {
                                            Storage::disk('local')->put('log2.html', gettype($row['left']) . '<br>' . json_encode($row['left']));
                                            $teamLeftName = $row['left'][0];
                                            $teamLeftRight = $row['left'][1];
                                        }
                                        if (array_key_exists('mid', $row)) {
                                            $teamDrawText = $row['mid'][0];
                                            $teamDrawScore = $row['mid'][1];
                                        }
                                        if (array_key_exists('right', $row)) {
                                            $teamRightName = $row['right'][0];
                                            $teamRightRight = $row['right'][1];
                                        }
                                        
                                        $matches[] = array('team_name' => $teamLeftName, 'score' => 0, 'water' => (float) $teamLeftRight);
                                        $matches[] = array('team_name' => $teamDrawText, 'score' => 0, 'water' => (float) $teamDrawScore);
                                        $matches[] = array('team_name' => $teamRightName, 'score' => 0, 'water' => (float) $teamRightRight);
                                    } else {
                                        $teamLeftMid = null;
                                        $teamRightMid = null;

                                        if (array_key_exists('left', $row)) {
                                            $teamLeftName = $row['left'][0];
                                            $teamLeftMid = $row['left'][1];
                                            $teamLeftRight = $row['left'][2];
                                        }

                                        if (array_key_exists('right', $row)) {
                                            $teamRightName = $row['right'][0];
                                            $teamRightMid = $row['right'][1];
                                            $teamRightRight = $row['right'][2];
                                        }

                                        $teamName = '';
                                        $score = 0.00;
                                        $water = 0.00;

                                        if ($teamLeftMid && $teamRightMid) {
                                            if (((float) $teamLeftMid == 0.00 || (float) $teamLeftMid == 0.0 || (float) $teamLeftMid == 0) && ((float) $teamRightMid == 0.00 || (float) $teamRightMid == 0.0 || (float) $teamRightMid == 0)) {
                                                $teamName = $teamLeftName;
                                                $score = 0.00;
                                                $water = (float) $teamLeftRight;
                                            } else {
                                                if ((float) $teamLeftMid < (float) $teamRightMid) {
                                                    $teamName = $teamLeftName;
                                                    $score = (float) $teamLeftMid;
                                                    $water = (float) $teamLeftRight;
                                                } else {
                                                    $teamName = $teamRightName;
                                                    $score = (float) $teamRightMid;
                                                    $water = (float) $teamRightRight;
                                                }
                                            }
                                        } else {
                                            if ((float) $teamLeftRight < (float) $teamRightRight) {
                                                $teamName = $teamLeftName;
                                                $score = 0;
                                                $water = (float) $teamLeftRight;
                                            } else {
                                                $teamName = $teamRightName;
                                                $score = 0;
                                                $water = (float) $teamRightRight;
                                            }
                                        }

                                        $matches[] = array('team_name' => $teamName, 'score' => $score, 'water' => $water);
                                    }                              
                                }
                            }
                        }
                    }

                    if ($topHead == 'Asian Handicap') {
                        $asianHandicap = array('date_time' => $currentDir,
                                                'matches' => $matches);
                        $countAsian++;
                    }
                    if ($topHead == 'Over Under') {
                        $overUnder = array('date_time' => $currentDir,
                                                'matches' => $matches);
                        $countOver++;
                    }
                    if ($topHead == '1X2') {
                        $onePlusTwo = array('date_time' => $currentDir,
                                                'matches' => $matches);
                        $countOne++;
                    }
                }
            }
        }

        if ($countAsian == 0) {
            $asianHandicap = array('date_time' => $currentDir,
                                    'matches' => array());
        }
        if ($countOver == 0) {
            $overUnder = array('date_time' => $currentDir,
                                    'matches' => array());
        }
        if ($countOne == 0) {
            $onePlusTwo = array('date_time' => $currentDir,
                                    'matches' => array());
        }

        return array('asian' => $asianHandicap, 'over' => $overUnder, 'one' => $onePlusTwo);
    }

    public function skudTopHead($content = '')
    {
        preg_match('/<div class="SubHead">(.*?)<\/div>/s', $content, $datas);
        $dtHead = (array_key_exists(1, $datas)) ? $datas[1] : '';
        preg_match("'<span>(.*?)</span>'si", $dtHead, $rows);
        $topHead = (array_key_exists(1, $rows)) ? $rows[1] : '';

        return $topHead;
    }

    public function skudOutput($htmlContent = '')
    {
        $result = preg_replace($this->attrWithLinkPatterns, '', $htmlContent);
        $finalHtml = preg_replace($this->removeLink, $this->rplByScript, $result);
        $finalHtml = preg_replace($this->textInClasspatterns, $this->textInClassReplaces, $finalHtml);
        $patternToReplace = array('/title/', '/" >/', '/OddsL\//', '/\/Draw/');
        $replaceWith = array('', '/">/', 'OddsL', 'Draw');
        $htmlForGraph = preg_replace($patternToReplace, $replaceWith, $finalHtml);

        $MarketT = explode('class="MarketT">', $htmlForGraph);
        array_shift($MarketT);
        // echo count($MarketT);

        return $MarketT;
    }

    public function skudYen($innerContent, $topHead)
    {
        $debugDraw = '';
        $tds = explode('<tr>', $innerContent);
        // echo $innerContent;
        array_shift($tds);
        $tdDatas = $tds[0];
        $expTd = explode('</td>', $tdDatas);
        array_pop($expTd);
        // echo count($expTd);
        // debug($expTd);
        
        // start team left data
        $teamLeft = (array_key_exists(0, $expTd)) ? $expTd[0] : '';

        if ($topHead == '1X2') {
            // Log::info($teamLeft);
        }

        preg_match('/<span class="OddsL">(.*?)<\/span>/s', $teamLeft, $rwsLL);
        preg_match('/<span class="OddsM">(.*?)<\/span>/s', $teamLeft, $rwsLM);
        preg_match('/<span class="OddsR">(.*?)<\/span>/s', $teamLeft, $rwsLR);

        $teamLeftName = (array_key_exists(1, $rwsLL)) ? $rwsLL[1] : '';
        $teamLeftMid = (array_key_exists(1, $rwsLM)) ? $rwsLM[1] : '';
        $teamLeftRight = (array_key_exists(1, $rwsLR)) ? $rwsLR[1] : '';
        $teamDrawText = '';
        $teamDrawScore = '';
        $teamRightName = '';
        $teamRightMid = '';
        $teamRightRight = '';

        if (count($expTd) < 3) {
            // does not has draw
            $teamRight = (array_key_exists(1, $expTd)) ? $expTd[1] : '';

            // start team right data
            preg_match('/<span class="OddsL">(.*?)<\/span>/s', $teamRight, $rwsRL);
            preg_match('/<span class="OddsM">(.*?)<\/span>/s', $teamRight, $rwsRM);
            preg_match('/<span class="OddsR">(.*?)<\/span>/s', $teamRight, $rwsRR);

            $teamRightName = (array_key_exists(1, $rwsRL)) ? $rwsRL[1] : '';
            $teamRightMid = (array_key_exists(1, $rwsRM)) ? $rwsRM[1] : '';
            $teamRightRight = (array_key_exists(1, $rwsRR)) ? $rwsRR[1] : '';
            // end team right data
        } else {
            // has draw
            $teamDraw = (array_key_exists(1, $expTd)) ? $expTd[1] : '';
            $teamDraw = preg_replace('/title=\\"[^\\"]*\\"/', '', $teamDraw);
            // $teamDraw = preg_replace('/" >/', '">', $teamDraw);
            $teamDraw = preg_replace('/\s+/', '', $teamDraw);
            $teamDraw = preg_replace('/=""/', '', $teamDraw);
            $teamDraw = preg_replace('/spanclass/', 'span class', $teamDraw);
            $debugDraw = $teamDraw;

            // start team draw data
            preg_match('/<span class="OddsL">(.*?)<\/span>/s', $teamDraw, $rwsDL);
            preg_match('/<span class="OddsR">(.*?)<\/span>/s', $teamDraw, $rwsDR);

            $teamDrawText = (array_key_exists(1, $rwsDL)) ? $rwsDL[1] : '';
            $teamDrawScore = (array_key_exists(1, $rwsDR)) ? $rwsDR[1] : '';
            // end team draw data

            $teamRight = (array_key_exists(2, $expTd)) ? $expTd[2] : '';

            // start team right data
            preg_match('/<span class="OddsL">(.*?)<\/span>/s', $teamRight, $rwsRL);
            preg_match('/<span class="OddsM">(.*?)<\/span>/s', $teamRight, $rwsRM);
            preg_match('/<span class="OddsR">(.*?)<\/span>/s', $teamRight, $rwsRR);

            $teamRightName = (array_key_exists(1, $rwsRL)) ? $rwsRL[1] : '';
            $teamRightMid = (array_key_exists(1, $rwsRM)) ? $rwsRM[1] : '';
            $teamRightRight = (array_key_exists(1, $rwsRR)) ? $rwsRR[1] : '';
            // end team right data
        }

        $rowDatas = array('team_left' => $teamLeftName,
                            'score_left_mid' => $teamLeftMid,
                            'score_left_last' => $teamLeftRight,
                            'draw_text' => $teamDrawText,
                            'draw_score' => $teamDrawScore,
                            'team_right' => $teamRightName,
                            'score_right_mid' => $teamRightMid,
                            'score_right_last' => $teamRightRight,
                            'debugDraw' => $debugDraw);

        return $rowDatas;
    }

    public function dirList(Request $request)
    {
        $dirLatestList = array();

        $linkCode = $request->link;
        $dirName = substr($linkCode, 0, 13);
        $fileName = substr($linkCode, 14, (strlen($linkCode) - 1));

        $linkDatas = ContentDetail::select('link')->where('dir_name', $dirName)->where('file_name', $fileName);

        if ($linkDatas->count() > 0) {
            $links = $linkDatas->get();
            $link = $links[0]->link;

            $dirListDatas = ContentDetail::select('dir_name')->groupBy('dir_name');

            if ($dirListDatas->count() > 0) {
                $dirtList = $dirListDatas->get();

                foreach($dirtList as $row) {
                    $checkDirDatas = ContentDetail::select('dir_name')->where('dir_name', $row->dir_name)->where('link', $link);
                    if ($checkDirDatas->count() > 0) {
                        $dirLatestList[] = $row;
                    }
                }
            }
        }

        return response()->json($dirLatestList);
    }

    public function currentDetailcontent(Request $request)
    {
        $detailId = $request->detail_id;

        $datas = $this->currentDetailcontentByDetailId($detailId);

        return response()->json($datas);
    }

    public function currentDetailcontentByDetailId($detailId = 0)
    {
        $dirName = '';
        $homeTeamName = '';
        $homeTeamLogo = '';
        $awayTeamName = '';
        $awayTeamLogo = '';
        $leagueName = '';
        $eventTime = '';
        $latestContent = '';

        $asianMatches = array();
        $overMatches = array();
        $oneMatches = array();

        $groupDatas = array();

        // , 'content'
        $latestCurrentDatas = ContentDetail::select(['dir_name', 'league_name', 'vs', 'event_time'])->where('id', $detailId);

        if ($latestCurrentDatas->count() > 0) {
            $latestDatas = $latestCurrentDatas->get();

            $dirName = $latestDatas[0]->dir_name;
            $vs = $latestDatas[0]->vs;
            if (trim($vs) && !empty($vs)) {
                $vsList = preg_split('/-vs-/', $vs);
                $homeTeamName = $vsList[0];
                $awayTeamName = array_key_exists(1, $vsList) ? $vsList[1] : '-';

                $homeTeamLogo = $this->logoByTeamName($homeTeamName);

                if ($awayTeamName != '-') {
                    $awayTeamLogo = $this->logoByTeamName($awayTeamName);
                }
            }

            $leagueName = $latestDatas[0]->league_name;
            $eventTime = $latestDatas[0]->event_time;

            // $latestContent = $latestDatas[0]->content;
            // $groupDatas = $this->arrangeGraphDatas($dirName, $latestContent);

            // --- start arrange --- //
            /*
            $output = $this->skudOutput($latestContent);

            if (count($output) > 0) {
                foreach ($output as $k => $content) {
                    $topHead = $this->skudTopHead($content);

                    // --- start store [A, O, O]
                    if ($topHead == 'Asian Handicap' || $topHead == 'Over Under' || $topHead == '1X2') {
                        $bdDatas = explode('class="MarketBd">', $content);
                        array_shift($bdDatas);
                        $tableContent = $bdDatas[0];
    
                        $tableDatas = explode('</tr>', $tableContent);
                        array_pop($tableDatas);
                        $tableDatas = $tableDatas; // array_slice($tableDatas, 0, 1);
                        // debug($tableDatas);
    
                        if (count($tableDatas) > 0) {
                            $matches = array();
                            foreach($tableDatas as $innerContent) {
                                $allVariables = $this->skudYen($innerContent, $topHead);
                                $matches[] = $allVariables;
                            }

                            if ($topHead == 'Asian Handicap') {
                                $asianMatches = $matches;
                            }
                            if ($topHead == 'Over Under') {
                                $overMatches = $matches;
                            }
                            if ($topHead == '1X2') {
                                $oneMatches = $matches;
                            }
                        }
                    }
                }
            }
            */
        }

        /*
        $asianContent = array('top_head' => 'Asian Handicap',
                                'matches' => $asianMatches);
        $overContent = array('top_head' => 'Over Under',
                                'matches' => $overMatches);
        $oneContent = array('top_head' => '1X2',
                                'matches' => $oneMatches);
        */

        $datas = array('league_name' => $leagueName,
                        'home_team' => $homeTeamName,
                        'home_logo' => $homeTeamLogo,
                        'away_team' => $awayTeamName,
                        'away_logo' => $awayTeamLogo,
                        'event_time' => $eventTime);

                        /*,
                        'asian_content' => $groupDatas['asian'],
                        'over_content' => $groupDatas['over'],
                        'one_content' => $groupDatas['one']); // , 'latest_content' => $latestContent
                        */

        return $datas;
    }

    public function prediction()
    {
        $datas = array();
        $successList = array();

        $curDatas = $this->realCurrentContent();
        $found = $curDatas['found'];
        $dirName = $curDatas['dirName'];
        $latestContent = $curDatas['latestContent'];

        if ($found == 1) {
            $detailDatas = ContentDetail::select(['id', 'league_name', 'vs', 'event_time', 'link'])->where('dir_name', $dirName)->orderBy('code', 'asc');

            if ($detailDatas->count() > 0) {
                // --- start special query --- //
                $dayList = ContentDetail::select('dir_name')->groupBy('dir_name')->orderBy('dir_name', 'asc');
                $totalInner = $dayList->count();
                if ($totalInner > 0) {
                    $dirList = $dayList->get();
                    foreach($dirList as $key => $dName) {
                        $dlDatas = dirList::select('dir_name')->where('scraping_status', '1')->where('dir_name', $dName->dir_name);
                        if ($dlDatas->count() > 0) {
                            $successList[] = $dName->dir_name;
                        }
                    }
                }
                // --- end special query --- //

                foreach($detailDatas->get() as $k => $v) {
                    $detailId = $v->id;
                    $leagueName = $v->league_name;
                    $eventTime = $v->event_time;
                    $vs = $v->vs;
                    $homeTeamName = '';
                    $awayTeamName = '';
                    
                    if (trim($vs) && !empty($vs)) {
                        $vsList = preg_split('/-vs-/', $vs);
                        $homeTeamName = $vsList[0];
                        $awayTeamName = array_key_exists(1, $vsList) ? $vsList[1] : '-';
                    }

                    $datas[] = array('league_name' => $leagueName,
                                    'event_time' => $eventTime,
                                    'home_team' => $homeTeamName,
                                    'away_team' => $awayTeamName,
                                    // 'datas' => $this->graphFromLinkNew($v->link, $successList),
                                    'link' => $v->link,
                                    'id' => $detailId);
                }
            }
        }

        $lList = array();
        $structureDatas = array();

        if (count($datas) > 0) {
            foreach($datas as $data) {
                if (! in_array($data['league_name'], $lList)) {
                    $lList[] = $data['league_name'];
                }
            }

            foreach($lList as $league_name) {
                $rows = array();
                foreach($datas as $data) {
                    if ($data['league_name'] == $league_name) {
                        $rows[] = $data;
                    }
                }
                
                $structureDatas[] = array('league_name' => $league_name, 'rows' => $rows);
            }
        }

        $mainDatas = array('success_list' => $successList, 'datas' => $structureDatas);

        return response()->json($mainDatas);
    }

    public function predictionPrice(Request $request)
    {
        $link = $request->link;
        $successList = $request->success_list;

        $datas = $this->graphFromLinkNew($link, $successList);

        return response()->json($datas);
    }

    public function graphFromLinkNew($realLink, $successList)
    {
        $asianScore = null;
        $asianWater = null;
        $overScore = null;
        $overWater = null;

        $foundAsian = 0;
        $foundOver = 0;

        if (count($successList) > 0) {
            $contentDatas = ContentDetail::select(['content'])->where('link', $realLink);
            $contentDatas->whereNotNull('content');
            $contentDatas->whereIn('dir_name', $successList);
            $contentDatas->orderBy('file_name', 'asc');

            if ($contentDatas->count() > 0) {
                foreach($contentDatas->get() as $row) {
                    $htmlContent = $row->content;

                    // --- start skud score --- //
                    if ($htmlContent) {
                        $tableDatas = json_decode($htmlContent);

                        if ($tableDatas) {
                            if (count($tableDatas) > 0) {
                                foreach($tableDatas as $innerContent) {
                                    $topHead = $innerContent->top_head;
                                    if ($innerContent->datas) {
                                        $matches = array();
                                        if (count($innerContent->datas) > 0) {
                                            $gDatas = $innerContent->datas;
                                            $data = $gDatas[0];

                                            if ($data) {
                                                $row = (array) $data;
            
                                                if ($topHead != '1X2') {
                                                    $teamLeftMid = null;
                                                    $teamRightMid = null;
            
                                                    if (array_key_exists('left', $row)) {
                                                        $teamLeftMid = $row['left'][1];
                                                        $teamLeftRight = $row['left'][2];
                                                    }
            
                                                    if (array_key_exists('right', $row)) {
                                                        $teamRightMid = $row['right'][1];
                                                        $teamRightRight = $row['right'][2];
                                                    }
            
                                                    $score = 0.00;
                                                    $water = 0.00;
            
                                                    if ($teamLeftMid && $teamRightMid) {
                                                        if (((float) $teamLeftMid == 0.00 || (float) $teamLeftMid == 0.0 || (float) $teamLeftMid == 0) && ((float) $teamRightMid == 0.00 || (float) $teamRightMid == 0.0 || (float) $teamRightMid == 0)) {
                                                            $score = 0.00;
                                                            $water = (float) $teamLeftRight;
                                                        } else {
                                                            if ((float) $teamLeftMid < (float) $teamRightMid) {
                                                                $score = (float) $teamLeftMid;
                                                                $water = (float) $teamLeftRight;
                                                            } else {
                                                                $score = (float) $teamRightMid;
                                                                $water = (float) $teamRightRight;
                                                            }
                                                        }
                                                    } else {
                                                        if ((float) $teamLeftRight < (float) $teamRightRight) {
                                                            $score = 0;
                                                            $water = (float) $teamLeftRight;
                                                        } else {
                                                            $score = 0;
                                                            $water = (float) $teamRightRight;
                                                        }
                                                    }

                                                    if ($water > 0 && $water < 2) {
                                                        if ($topHead == 'Asian Handicap' && $foundAsian == 0) {
                                                            $asianScore = $score;
                                                            $asianWater = $water;
                                                            $foundAsian = 1;
                                                        }
                                                        if ($topHead == 'Over Under' && $foundOver == 0) {
                                                            $overScore = $score;
                                                            $overWater = $water;
                                                            $foundOver = 1;
                                                        }
                                                    }
                                                }                              
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // --- end skud score --- //

                }
            }
        }

        $asianHandicap = array('score' => $asianScore, 'water' => $asianWater);
        $overUnder = array('score' => $overScore, 'water' => $overWater);

        return array('asian' => $asianHandicap, 'over' => $overUnder);
    }

    // --------- start unused API --------- //
    public function logoByTeamName($teamName = '')
    {
        $fileName = '';
        $teamLogo = '';
        $teamLogoPath = '';
        $qStrHomeLogos = DB::table('ffp_logos')->select(['file_path', 'file_name'])->where('team_name', $teamName);

        if ($qStrHomeLogos->count() > 0) {
            $logoRows = $qStrHomeLogos->get();
            $logoRow = $logoRows[0];
            if ($logoRow->file_name) {
                $fileName = $logoRow->file_name;
                $teamLogo = $logoRow->file_path . '/' . $fileName;
            }
        }

        if ($teamLogo) {
            if ($fileName && file_exists(public_path().'/storage/' . $teamLogo)) {
                $teamLogoPath = asset('storage/' . $teamLogo);
            } else {
                $teamLogoPath = asset('images/bournemouth.png');
            }
        } else {
            $teamLogoPath = asset('images/bournemouth.png');
        }

        return $teamLogoPath;
    }

    public function contentDetail(Request $request)
    {
        $masterData = array();
        $contentList = array();

        $linkCode = $request->link;
        $fileName = substr($linkCode, 14, (strlen($linkCode) - 1));
        $dirName = $request->dir_name;

        $realLink = '';
        $findLinkDatas = ContentDetail::select(['link'])->where('dir_name', $dirName)->where('file_name', $fileName);

        if ($findLinkDatas->count() > 0) {
            $row = $findLinkDatas->get();
            $realLink = $row[0]->link;

            $contentDatas = ContentDetail::select(['id', 'dir_name', 'file_name', 'content'])->where('link', $realLink)->where('dir_name', $dirName);

            if ($contentDatas->count() > 0) {
                $rows = $contentDatas->get();
                $masterData = $rows[0];

                // $message = 'ID: ' . $masterData->id;
                // Log::info($message);

                $contentList = $this->arrangeContentDetail($masterData->content);

                $datas = array('master_data' => $masterData, 'arrange_data' => $contentList);
                return response()->json($datas);
            } else {
                $datas = array('master_data' => $masterData, 'arrange_data' => $contentList);
                return response()->json($datas);
            }
        } else {
            $datas = array('master_data' => $masterData, 'arrange_data' => $contentList);
            return response()->json($datas);
        }
    }

    public function arrangeContentDetail($htmlContent = '')
    {
        $headList = array();

        if ($htmlContent) {
            $result = preg_replace($this->attrWithLinkPatterns, '', $htmlContent);
            $finalHtml = preg_replace($this->removeLink, $this->rplByScript, $result);
            $finalHtml = preg_replace($this->textInClasspatterns, $this->textInClassReplaces, $finalHtml);
            $patternToReplace = array('/title/', '/" >/', '/OddsL\//', '/\/Draw/');
            $replaceWith = array('', '/">/', 'OddsL', 'Draw');
            $htmlForGraph = preg_replace($patternToReplace, $replaceWith, $finalHtml);

            $MarketT = explode('class="MarketT">', $htmlForGraph);
            array_shift($MarketT);
            // echo count($MarketT);

            $output = $MarketT; // array_slice($MarketT, 0, 3); // comment later

            if (count($output) > 0) {
                foreach ($output as $k => $content) {
                    preg_match('/<div class="SubHead">(.*?)<\/div>/s', $content, $datas);
                    $dtHead = (array_key_exists(1, $datas)) ? $datas[1] : '';
                    preg_match("'<span>(.*?)</span>'si", $dtHead, $rows);
                    $topHead = (array_key_exists(1, $rows)) ? $rows[1] : '';

                    $bdDatas = explode('class="MarketBd">', $content);
                    array_shift($bdDatas);
                    $tableContent = $bdDatas[0];

                    $tableDatas = explode('</tr>', $tableContent);
                    array_pop($tableDatas);
                    $tableDatas = $tableDatas; // array_slice($tableDatas, 0, 1);
                    // debug($tableDatas);

                    if (count($tableDatas) > 0) {
                        $matches = array();
                        foreach($tableDatas as $innerContent) {
                            $allVariables = $this->skudYen($innerContent, $topHead);
                            $teamLeftName = $allVariables['team_left'];
                            $teamLeftMid = $allVariables['score_left_mid'];
                            $teamLeftRight = $allVariables['score_left_last'];
                            $teamDrawText = $allVariables['draw_text'];
                            $teamDrawScore = $allVariables['draw_score'];
                            $teamRightName = $allVariables['team_right'];
                            $teamRightMid = $allVariables['score_right_mid'];
                            $teamRightRight = $allVariables['score_right_last'];

                            $matches[] = array('team_left' => $teamLeftName,
                                                'score_left_mid' => $teamLeftMid,
                                                'score_left_last' => $teamLeftRight,
                                                'draw_text' => $teamDrawText,
                                                'draw_score' => $teamDrawScore,
                                                'team_right' => $teamRightName,
                                                'score_right_mid' => $teamRightMid,
                                                'score_right_last' => $teamRightRight);
                        }
                    }

                    $headList[] = array('top_head' => $topHead, 'matches' => $matches);
                }
            }
        }

        return $headList;
    }

    public function saveToMatchList()
    {
        $fileList = array();
        $qStrFile = DB::table('ffp_file')->select('link_code');
        if ($qStrFile->count() > 0) {
            $fileDatas = $qStrFile->get();
            foreach($fileDatas as $file) {
                $fileList[] = $file->link_code;
            }
        }

        // 'ffp_detail.id', 
        $findBlankQuery = DB::table('ffp_detail')->select('ffp_detail.content', DB::raw('CONCAT(ffp_detail.dir_name, "-", ffp_detail.file_name) as link_code'));
        $datas = $findBlankQuery->whereNotIn(DB::raw('CONCAT(ffp_detail.dir_name, "-", ffp_detail.file_name)'), $fileList);
        // $datas = $findBlankQuery->orderBy('ffp_detail.id', 'desc');
        $datas = $findBlankQuery->take(10)->get();

        if (count($datas) > 0) {
            foreach($datas as $data) {
                $this->saveInToFileDB($data);
            }
        }
    }

    public function saveInToFileDB($data)
    {
        // $id = $data->id;
        $linkCode = $data->link_code;
        $content = $data->content;

        $rowDatas = $this->arrangeContentDetail($content);

        if (count($rowDatas) > 0) {
            foreach($rowDatas as $head) {
                $betType = '';
                $topHead = trim($head['top_head']);
                if ($topHead == 'Asian Handicap') {
                    $betType = 'asian';
                } else if ($topHead == 'Over Under') {
                    $betType = 'over';
                } else if ($topHead == '1X2') {
                    $betType = 'one';
                }

                if (! empty($betType)) {
                    if (count($head['matches']) > 0) {
                        foreach($head['matches'] as $match) {
                            if (trim($match['team_left'])) {
                                $fileSave = new FileDetail;
                                $fileSave->link_code = $linkCode;
                                $fileSave->bet_type = $betType;
                                $fileSave->home_team = $match['team_left'];
                                $fileSave->home_mid_score = $match['score_left_mid'];
                                $fileSave->home_water = $match['score_left_last'];
                                $fileSave->draw_text = $match['draw_text'];
                                $fileSave->draw_score = $match['draw_score'];
                                $fileSave->away_team = $match['team_right'];
                                $fileSave->away_mid_score = $match['score_right_mid'];
                                $fileSave->away_water = $match['score_right_last'];
        
                                $saved = $fileSave->save();
        
                                // dd($match);
        
                                if ($fileSave->id) {
                                    // ...
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*
    public function genLinkCode()
    {
        $total = 0;
        $done = 0;
        $contentDatas = ContentDetail::select('id', 'link')->whereNull('link_code')->take(10);
        $total = $contentDatas->count();
        if ($total > 0) {
            $contentList = $contentDatas->get();
            foreach($contentList as $data) {
                $id = $data->id;
                $link = $data->link;
                $linkCode = $id . '-';
                if (strlen(trim($link)) > 0) {
                    $linkCode .= substr(trim($link), 15, 18);

                    $data = ContentDetail::find($id);
                    $data->link_code = $linkCode;
                    $saved = $data->save();
                    $done++;
                }
            }
        }

        $datas = array('total' => $total, 'done' => $done);

        return response()->json($datas);
    }
    */

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
}
