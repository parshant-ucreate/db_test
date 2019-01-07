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
    public function index()
    {
	   $database_list = DB::select('select t1.datname AS name,  
                                pg_size_pretty(pg_database_size(t1.datname)) as db_size
                                from pg_database t1 WHERE datistemplate = false
                                order by pg_database_size(t1.datname) desc;');   
       return view('home', compact('database_list'));
    }


    public function create_database()
    {

        if (request()->isMethod('post')) {
            
            $validator = Validator::make(request()->all(), [
                'name' => 'required|alpha_dash'
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $name = strtolower(request()->name);

            $response = DB::statement('CREATE DATABASE '.$name);

            //dd($response);
            if($response){
                echo 'Success';
                die;
            }

        }
   
       return view('create_database');
    }

}
