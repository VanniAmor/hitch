<?php

namespace App\Jobs;

class ExampleJob extends Job
{
    
    protected $driver;
    protected $type;

    public function __construct($driver, $type)
    {
        $this->driver = $driver;
    }

    /**
     * Execute the job.
     * 
     * @return void
     */
    public function handle()
    {
        $key = $this->driver->redis_key;
        $value = $this->driver->did;

        if($this->type == 0 ){
            //删除司机
            app('redis')->lrem($key, 0, $value);
        }else{
            //添加司机
            app('redis')->lpush($key, $value);
        }
    }
}
