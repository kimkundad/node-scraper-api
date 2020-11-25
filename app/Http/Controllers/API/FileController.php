<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\CommonController;
use Storage;

class FileController extends Controller
{
    private $common;
    private $rootPath;

    public function __construct()
    {
        // $this->conn = CheckSystemController::checkTableExist('menus');
        $this->common = new CommonController();
        $this->rootPath = $_SERVER['DOCUMENT_ROOT'] . '/storage';

        // if ($this->conn['table']) {
        //     $this->menus = Menu::allMenus();
        // }
    }

    public function dooFile($file_name = '')
    {
        // show content in file => log.html
        // $path = asset('storage/' . $file_name); // http://localhost/storage/log.html
        // $content = Storage::disk('local')->get($file_name);

        // show image path
        $path = asset('storage/logos/' . $file_name); // Newcastle-United.png
        
        return response()->json(array('logo_list' => $logoList)); // , 'content' => $content
    }

    public function logos($directory = '')
    {
        $logoList = array();

        // --- start list files in directory --- //

        // --- start code important file --- //
        $root_path = $this->rootPath . '/' . $directory;
        $http_host = url('/') . '/public';

        $GLOBALS['exclude_folders'] = array();

        @set_time_limit(600);
        date_default_timezone_set('Asia/Bangkok');
        ini_set('default_charset', 'UTF-8');

        if (version_compare(PHP_VERSION, '5.6.0', '<') && function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
        if (function_exists('mb_regex_encoding')) {
            mb_regex_encoding('UTF-8');
        }

        $is_https = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
        || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

        $root_path = rtrim($root_path, '\\/');
        $root_path = str_replace('\\', '/', $root_path);

        if (!@is_dir($root_path)) {
            echo "<h1>Root path \"{$root_path}\" not found!</h1>";
            exit;
        }

        $root_url = $this->common::fm_clean_path('');

        $preHttp = $http_host . (!empty($root_url) ? '/' . $root_url : '');
        $selfUrl = $http_host . $_SERVER['PHP_SELF'];

        defined('FM_ROOT_URL') || define('FM_ROOT_URL', $preHttp);
        defined('FM_SELF_URL') || define('FM_SELF_URL', $selfUrl);

        define('FM_IS_WIN', DIRECTORY_SEPARATOR == '\\');

        if (!is_dir($root_path)) {
            fm_redirect(FM_SELF_URL . '?p=');
        }

        $objects = is_readable($root_path) ? scandir($root_path) : array();
        $folders = array();
        $files = array();

        $flse = true;

        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file == '.' || $file == '..' && in_array($file, $GLOBALS['exclude_folders'])) {
                    continue;
                }
                if (!$flse && substr($file, 0, 1) === '.') {
                    continue;
                }

                $new_path = $root_path . '/' . $file;
                if (is_file($new_path)) {
                    $files[] = $file;
                } elseif (is_dir($new_path) && $file != '.' && $file != '..' && !in_array($file, $GLOBALS['exclude_folders'])) {
                    $folders[] = $file;
                }
            }
        }

        if (!empty($files)) {
            natcasesort($files);
        }

        if (!empty($folders)) {
            natcasesort($folders);
        }
        // --- end code important file --- //

        $variables = array('folders' => $folders,
            'files' => $files,
            'path' => $root_path,
            'directory' => $directory,
            'http_host' => $http_host
        );
        // --- end list files in directory --- //

        return response()->json($variables);
        // return view('backend/file-manager/files', $variables);
    }
}
