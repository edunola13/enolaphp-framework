<?php
namespace Enola\Commands;
use Enola\Cron;
use Enola\Cron\Models\En_CronRequest, Enola\Support\Generic\Response;

class ConfigCompile extends Cron\Models\En_CronController{
    
    public function compileAllConfig(En_CronRequest $request, Response $response){
        $files= \Enola\Support\get_files_from_folder($this->context->getConfigurationFolder(), true, array('yml', 'json'));
        foreach ($files as $file) {
            $this->context->compileConfigurationFile($file, true);
        }
    }
}