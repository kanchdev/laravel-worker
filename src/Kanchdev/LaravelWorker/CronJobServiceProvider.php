<?php

namespace Kanchdev\LaravelWorker;


use Illuminate\Queue\Console\WorkCommand;
use Kanchdev\LaravelWorker\CronJobWorker;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;

class CronJobServiceProvider extends QueueServiceProvider
{
    protected $supportedBaseCommands = [
        WorkCommand::class,
        'command.queue.work',
    ];

    public function register()
    {
        parent::register();

        $this->registerCronWorker();

        $this->registerCommand();

    }

    protected function registerCronWorker()
    {

        if($this->app->bound($instName = 'queue.cron.jobs')):
            return;
        endif;

        $this->app->singleton($instName, function () {
            return new CronJobWorker($this->app['queue'], $this->app['events'],
                $this->app[ExceptionHandler::class],
                fn () => $this->app->isDownForMaintenance()
            );
        });

    }

    protected function registerCommand()
    {

        foreach($this->supportedBaseCommands as $command):

            if($this->app->bound($command)):

                $this->app->extend($command, function ($command, Application $app) {
                    return new CronJobWorkCommand($app['queue.cron.jobs'], $app['cache.store']);

                });

                break;
            endif;

        endforeach;

    }
}
