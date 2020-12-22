<?php

namespace App\Console\Commands;

use App\Module;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class UpdateModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Module::query()->truncate();
        $this->info('Module table truncated successfully!');
        $this->info('Generating modules...');
        $routes = \Route::getRoutes();
        $modulesArray = [];
        foreach($routes->getRoutes() as $key => $route) {
            if(@$route->action['module'] != null){
                if(!array_key_exists($route->action['module'], $modulesArray)){
                    $modulesArray[$route->action['module']] = [
                                        'name' => ucwords(str_replace('_',' ',$route->action['module'])),
                                        'slug' => $route->action['module'],
                                        'icon' => $route->action['icon'],
                                        'status' => 1,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ];
                }
            }
        }
        DB::table('modules')->insert(array_values($modulesArray));
        $this->info('Module generated successfully!');
    }
}
