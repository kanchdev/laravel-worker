<?php

namespace Kanchdev\LaravelWorker\Console;

use Illuminate\Queue\Worker;
use Illuminate\Queue\Console\WorkCommand;
use Kanchdev\LaravelWorker\CronJobWorker;
use Illuminate\Contracts\Cache\Repository;

class CronJobWorkCommand extends WorkCommand
{
    /**
     * Number of jobs executed
     */
    protected int $jobsExecuted = 0;

    public function __construct(Worker $worker, Repository $cache)
    {

        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        // Get default max execution time - 5s
        $maxExecutionTime = ini_get('max_execution_time');
        $maxExecutionTime = $maxExecutionTime <= 0 ? 0 : $maxExecutionTime - 5;

        $this->signature .= '{--cron : Run custom job worker }
                            {--num=30 : Number of jobs to run}
                            {--seconds='.$maxExecutionTime.' : Number of seconds to run}';

        parent::__construct($worker, $cache);

    }

    protected function runWorker($connection, $queue)
    {

        if($this->option('cron')):
            return $this->executeJobs($connection, $queue);
        endif;

        return parent::runWorker($connection, $queue);
    }

    protected function executeJobs($connection, $queues)
    {
        /** @var CronJobWorker */
        $worker  = $this->worker;

        foreach(explode(',', $queues) as $queue):

            while($this->canRunNextJob() && $job = $worker->getNextCronJob($connection, $queue)):

                $worker->runCronJob($job, $connection, $this->gatherWorkerOptions());
                $this->jobsExecuted++;

            endwhile;

        endforeach;

    }

    protected function canRunNextJob()
    {

        if($this->isMaxExecutedTimePassed()):
            return false;
        endif;

        $numJobs = intval($this->option('num'));

        if($numJobs < $this->jobsExecuted):
            return false;
        endif;

        return true;

    }

    protected function isMaxExecutedTimePassed()
    {

        if(($seconds = intval($this->option('seconds'))) == 0):
            return false;
        endif;

        return  (time() - LARAVEL_START) > $seconds;

    }
}
