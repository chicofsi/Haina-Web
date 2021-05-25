<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Symfony\Component\Process\Process;

class WebHooks extends Controller
{
	public function index(Request $request)
	{
		$commands = array(
	        'ls'
	    );
	    $output = '';
	    foreach($commands AS $command){
	        // Run it
        	$migration = new Process([$command]);
        	$migration->setWorkingDirectory(base_path());
        	$migration->run();

	        // Output
	        $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
	        $output .= $migration->getOutput().'<br/>';
	    }
	    echo $output;

	}
}
