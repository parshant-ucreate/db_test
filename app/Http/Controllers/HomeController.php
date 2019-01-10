<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Redirect;
use Config;
use App\DbList;
use App\DbUser;

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
    
    public function generateRandomString($length = 7) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

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

    public function createDatabase() {
        if (request()->isMethod('post')) {
            $validator = Validator::make(request()->all(), [
                'name' => 'required|alpha_num'
            ]);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }
            $response = DB::statement('CREATE DATABASE '.strtolower(request()->name));
            if($response){
                $db_id = $this->saveDatabaseNameIntoAppDb(strtolower(request()->name));
                $db_user['admin'] = $this->createDatabaseSuperUser(strtolower(request()->name), $db_id->id);
                $db_user['normal'] = $this->createDatabaseNormalUser(strtolower(request()->name), $db_id->id);
                $db_user['read'] = $this->createDatabaseReadonlyUser(strtolower(request()->name), $db_id->id , $db_user['normal']);
                    
                if($this->saveDatabaseUsersInfoIntoAppDb($db_user)){
                    
                   return Redirect::to('/'.$db_id->name.'/details');
                } else {
                    echo 'Something went wrong'; die;
                }
            }
        }

       return view('create_database');
    }

    protected function saveDatabaseNameIntoAppDb($db_name) {
        return DbList::create([
            'name' => $db_name,
        ]);
    }

    protected function saveDatabaseUsersInfoIntoAppDb($users_info) {
        if(!empty($users_info)) {
            foreach ($users_info as $key => $value) {
               DbUser::create($value);
            }
            return true;
        }
        return false;
    }

    protected function createDatabaseSuperUser($db_name, $db_id) {
        $database['username'] = strtolower($this->generateRandomString().str_random(7));
        $database['password'] = strtolower(str_random(35));
        $this->createDbUser($database['username'], $database['password']);
        $this->grantDbConnectPermission($db_name, $database['username']);
        DB::select("grant all privileges on database ".$db_name." to ".$database['username'].";");
        $database['database_list_id'] = $db_id;
        $database['user_type'] = 'admin';
        return $database;
    }

    protected function createDatabaseNormalUser($db_name, $db_id) {
        $database['username'] = strtolower($this->generateRandomString().str_random(7));
        $database['password'] = strtolower(str_random(35));
        $this->createDbUser($database['username'], $database['password']); 
        $this->grantDbConnectPermission($db_name, $database['username']);
        
        //$conn = $this->swicthDatabase($db_name);
        //$conn->select("grant SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public to ".$database['username'].";"); 
        //$this->closeTempConection();

        $database['database_list_id'] = $db_id;
        $database['user_type'] = 'user';
        return $database;
    }

    protected function createDatabaseReadonlyUser($db_name, $db_id , array $owner ) {
        $database['username'] = strtolower($this->generateRandomString().str_random(7));
        $database['password'] = strtolower(str_random(35));
        $this->createDbUser($database['username'], $database['password']); 
        $this->grantDbConnectPermission($db_name, $database['username']);
        
        $conn = $this->swicthDatabase($db_name,$owner['username'],$owner['password']);
        $conn->select("GRANT USAGE ON SCHEMA public TO ".$database['username'].";"); 
        $conn->select("GRANT SELECT ON ALL TABLES IN SCHEMA public TO ".$database['username'].";"); 
        $conn->select("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO ".$database['username'].";"); 
        $this->closeTempConection();

        $database['database_list_id'] = $db_id;
        $database['user_type'] = 'readonly';
        return $database;
    }

    public function createReadOnly($db_name) {
    /*  $database['username'] = strtolower($this->generateRandomString().str_random(7));
        $database['password'] = strtolower(str_random(35));
        $conn = $this->swicthDatabase($db_name);

        dd($conn);

        $this->createDbUser($database['username'], $database['password']); 
        $this->grantDbConnectPermission($db_name, $database['username']);   
        
        $conn->select("GRANT USAGE ON SCHEMA public TO ".$database['username'].";"); 
        $conn->select("GRANT SELECT ON ALL TABLES IN SCHEMA public TO ".$database['username'].";"); 
        $conn->select("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO ".$database['username'].";"); 
        pr($database);*/
       die('here');
    }

    protected function createDbUser($user_name, $password) {
        DB::select("create user ".$user_name);
        return DB::select("ALTER USER ".$user_name." WITH PASSWORD '".$password."';"); 
    }

    protected function grantDbConnectPermission($db_name, $user_name) {
        DB::select("REVOKE ALL PRIVILEGES ON DATABASE ".$db_name." FROM public;"); 
        return DB::select("GRANT CONNECT ON DATABASE ".$db_name." TO ".$user_name.";"); 
    }

    protected function dropDatabase($db_name) {

        $database_result = DbList::whereName($db_name)->with('DbUser')->first();

        $user_ids = [];

        foreach ($database_result->DbUser as $key => $value) {

            DB::statement("REVOKE ALL PRIVILEGES ON DATABASE ".$db_name." FROM ".$value->username);
            
            if($value->user_type != 'admin'){
                DB::statement("REVOKE SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public FROM ".$value->username);
            }

            DB::statement("DROP ROLE ".$value->username);
            $user_ids[] = $value->id;
        }
        
        DB::statement("DROP DATABASE ".$db_name);

        if(count($user_ids)){
            DbUser::destroy($user_ids);
        }

        DbList::where('name', $db_name)->delete();

        return redirect()->route('home');
    }

    public function dbDetails($db_name) {
        $db_id = DbList::isDbExists($db_name);
        if($db_id) {
            $db_user = DbList::getDbDetails($db_id);
            return view('db_details', compact('db_user', 'db_name')); 
        }
    } 

    protected function swicthDatabase($db_name, $username = null,$password = null) {

        Config::set('database.connections.temp', array(
                    'driver' => env('DB_CONNECTION'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => $db_name,
                    'username' => $username ?? env('DB_USERNAME'),
                    'password' => $password ?? env('DB_PASSWORD'),
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'schema' => 'public',
                    'sslmode' => 'prefer',
                ));

        return DB::connection('temp');
    }

    protected function closeTempConection() {
        Config::set('database.connections.temp', array(
                    'driver' => env('DB_CONNECTION'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => '',
                    'username' => '',
                    'password' => '',
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'schema' => 'public',
                    'sslmode' => 'prefer',
                ));
        DB::disconnect('temp');
    }
}