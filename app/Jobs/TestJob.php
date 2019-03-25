<?php

namespace App\Jobs;

class TestJob extends Job
{

    private $str;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($str)
    {
        $this->str = $str;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo $this->str;
    }
}
