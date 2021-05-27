<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WebHooks extends Controller
{
	public function index(Request $request)
	{
		$commands =[['git', 'reset', '--hard', 'origin/master'],['git','pull']];
	    $output = '';
	    foreach ($commands as $command) {
		    $migration = new Process($command);
	    	$migration->setWorkingDirectory(base_path());
	    	$migration->run();
	    	if($migration->isSuccessful()){
	            $output.=$migration->getOutput();
	        } else {
	            throw new ProcessFailedException($migration);
	        }
	    }
	    echo $output;

	}
}
