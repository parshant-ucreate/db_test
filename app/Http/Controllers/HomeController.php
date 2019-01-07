<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Redirect;

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
    public function index() {
	   $database_list =   $this->fetchAllDatabaseListWithSize();
       return view('home', compact('database_list'));
    }


    protected function fetchAllDatabaseListWithSize() {
        return DB::select('select t1.datname AS name,  
                            pg_size_pretty(pg_database_size(t1.datname)) as db_size
                            from pg_database t1 WHERE datistemplate = false
                            order by pg_database_size(t1.datname) desc;'
                        ); 
    }

    public function create_database() {
        if (request()->isMethod('post')) {
            $validator = Validator::make(request()->all(), [
                'name' => 'required|alpha_num'
            ]);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }
            $response = DB::statement('CREATE DATABASE '.strtolower(request()->name));
            if($response){
                $d = $this->createDatabaseSuperUser(strtolower(request()->name));
                $a = $this->createDatabaseNormalUser(strtolower(request()->name));
                echo '<pre>'; print_r($d); echo '<br>'; echo '<pre>'; print_r($a); die;
                die;
            }
        }
       return view('create_database');
    }

    protected function createDatabaseSuperUser($db_name) {
        $database['user'] = str_random(10);
        $database['password'] = str_random(25);
        DB::select("create user ".$database['user']." with encrypted password '".$database['password']."';"); 
        DB::select("grant all privileges on database ".$db_name." to ".$database['user'].";"); 
        return $database;
    }

    protected function createDatabaseNormalUser($db_name) {
        $database['user'] = str_random(10);
        $database['password'] = str_random(25);
        DB::select("create user ".$database['user']." with encrypted password '".$database['password']."';"); 
        DB::select("grant SELECT, INSERT, UPDATE privileges on database ".$db_name." to ".$database['user'].";"); 
        return $database;
    }
}
