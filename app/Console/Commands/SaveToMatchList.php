<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\API\ContentDetailController as ContentDetail;

class SaveToMatchList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nscpapi:save-to-match-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For save ffp match every 5 minutes';
    private $contentDetail;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->contentDetail = new ContentDetail();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->contentDetail->saveToMatchList();
    }
}
