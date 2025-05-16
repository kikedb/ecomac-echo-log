<?php

namespace Ecomac\EchoLog\Console;

use Illuminate\Console\Command;
class  MonitorLogError extends Command
{
    protected $signature = 'ecomac:monitor-log-error';
    protected $description = 'Monitorea el log de Laravel y notifica errores repetitivos';

    public function handle()
    {
        $this->info('Comando ejecutado correctamente');
        $this->info($this->laravel['config']->get('echo-log.app_name'));
    }

}
