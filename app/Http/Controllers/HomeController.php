<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
	   $data = DB::select('SELECT datname FROM pg_database WHERE datistemplate = false');
	   echo '<pre>'; print_r($data); die;
	   //	    $response = \Terminal::command('sudo -u www-data ls')->execute();
//	    echo '<pre>'; print_r($response); die;
	    $output = array();
	    //echo system("sudo su"); die;
	    echo shell_exec("cd /var/www/html/; ./script.sh"); die;    
	    return view('home');
    }
}
