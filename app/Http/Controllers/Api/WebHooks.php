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
		$commands =['git','pull'];
	    $output = '';
	    $migration = new Process($commands);
    	$migration->setWorkingDirectory(base_path());
    	$migration->run();
    	if($migration->isSuccessful()){
            $output=$migration->getOutput();
        } else {
            throw new ProcessFailedException($migration);
        }
	    echo $output;

	}
}
