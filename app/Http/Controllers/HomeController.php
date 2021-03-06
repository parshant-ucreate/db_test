<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Redirect;
use Carbon\Carbon;
use Config;
use App\DbList;
use App\DbUser;
use App\DbBackup;
use App\DbRestorePoints;
use Illuminate\Support\Facades\Storage;
use App\Jobs\RunDatabaseBackup;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', '2fa']);
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
       $list = DbList::all()->pluck('name')->toArray();
       return view('home', compact('database_list','list'));
    }

    protected function fetchAllDatabaseListWithSize() {
        return DB::select('SELECT t1.datname AS name,pg_size_pretty(pg_database_size(t1.datname)) as db_size
                            from pg_database t1 WHERE datistemplate = false
                            order by pg_database_size(t1.datname) desc;'
                        ); 
    }

    public function createDatabase() {
        if (request()->isMethod('post')) {
            $validator = Validator::make(request()->all(), [
                'name' => 'required|alpha_num|unique:database_list'
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
        $this->grantReadOnlyUserPrivileges($conn,$database['username']); 
        $this->closeTempConection();

        $database['database_list_id'] = $db_id;
        $database['user_type'] = 'readonly';
        return $database;
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
        $dbUsers = ObjectToArray($database_result->DbUser);
        $this->dropExtensions($db_name);
        if(count($dbUsers)){
            foreach ($dbUsers as $key => $value) {
                if($value['user_type'] == 'user'){
                    $owner['username'] = $value['username'];
                    $owner['password'] = $value['password'];
                }
            }
            rsort($dbUsers);
            foreach ($dbUsers as $key => $value) {
                if($value['user_type'] == 'readonly'){
                    $conn = $this->swicthDatabase($db_name,$owner['username'],$owner['password']);
                    $this->revokeReadOnlyUserPrivileges($conn, $value['username']);
                    $this->closeTempConection();
                }
                DB::statement("REVOKE ALL PRIVILEGES ON DATABASE ".$db_name." FROM ".$value['username']);
                $this->dropUser($db_name,$value['username']);
                DbUser::destroy($value['id']);
            }
        }
        $this->dropDb($db_name);
        DbList::where('name', $db_name)->delete();
        return redirect()->route('home');
    }

    protected function dropExtensions($db_name){
        $conn = $this->swicthDatabase($db_name);
        $conn->statement('DROP EXTENSION IF EXISTS pg_stat_statements');
        $this->closeTempConection();
    }
    
    protected function grantReadOnlyUserPrivileges($conn, $username) {
        $conn->select("GRANT USAGE ON SCHEMA public TO ".$username.";"); 
        $conn->select("GRANT SELECT ON ALL TABLES IN SCHEMA public TO ".$username.";"); 
        $conn->select("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO ".$username.";");  
    }

    protected function revokeReadOnlyUserPrivileges($conn, $username) {
        $conn->select("REVOKE USAGE ON SCHEMA public FROM ".$username.";"); 
        $conn->select("REVOKE SELECT ON ALL TABLES IN SCHEMA public FROM ".$username.";"); 
        $conn->select("ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE SELECT ON TABLES FROM ".$username.";"); 
    }

    protected function dropUser($db_name,$username) {
        $conn = $this->swicthDatabase($db_name);
        $conn->statement("SELECT oid, pg_encoding_to_char(encoding) AS encoding, datlastsysoid FROM pg_database WHERE datname='".$db_name."'");
        $conn->statement("DROP OWNED BY ".$username);
        $conn->statement("DROP ROLE ".$username);
        $this->closeTempConection();
    }

    protected function dropDb($db_name) {
        DB::statement("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '".$db_name."';");
        DB::statement("DROP DATABASE ".$db_name);
    }

    public function dbDetails($db_name) {
        $db_user = DbList::where('name', $db_name)->with('dbUser')->firstOrFail();
        return view('db_details', compact('db_user', 'db_name')); 
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

    public function getDatabaseLogFiles($path) {
        if(function_exists("scandir")) {
            return scandir($path);
        } else {
            $dh  = opendir($path);
            while (false !== ($filename = readdir($dh)))
                $files[] = $filename;
            return $files;
        }
    }

    public function showDatabaseLogs() {
        $log_files = $this->getDatabaseLogFiles(getenv('DATABASE_LOGS_DIRECTORY'));
        $log_file_url = "";
        if($log_files[2]) {
            $log_file_url = getenv('DATABASE_LOGS_DIRECTORY').$log_files[2]; 
        }
        return view('db_log', compact('log_file_url'));
    }

    protected function backupDatabase($db_name) {
        if (!is_dir('db_backup/')) {
            $oldmask = umask(0);
            mkdir("db_backup", 0777);
            umask($oldmask);        
        }

        $db = DbList::where('name', $db_name)->firstOrFail();

        RunDatabaseBackup::dispatch($db,'manual');
  
        return redirect()->route('db_details',$db_name);
    }

    protected function importDatabase($db_name) {
        $database = DbList::whereName($db_name)->with(['dbBackup','dbRestorePoints'])->firstOrFail();
        $success = false;
        if (request()->isMethod('post')) {
            request()->validate(['url' => 'required|url' ]);
            $filename = last(explode('/', request()->url));
            $success = $this->restoreDatabase($database,$db_name,$filename);
        }
        return view('import_database', compact('db_name','success','database'));
    }

    public function restoreDatabase($database,$db_name,$filename){
        $db_backup = DbBackup::backupExist($filename);
        if(!$db_backup){
            return Redirect::back()->withErrors(['url' => 'Invalid url'])->withInput();
        }
        $ext = last(explode('.', $filename));
        $url = $db_name.'/'.$filename;
        $exists = Storage::disk('s3')->exists($url);
        if(!$exists){
            return Redirect::back()->withErrors(['url' => 'Invalid url'])->withInput();
        }
        RunDatabaseBackup::dispatch($database,'restore',$db_backup->id);
        $file =  Storage::disk('s3')->get($url);
        Storage::append($filename, $file);
        $path = Storage::path($filename);
        $this->dropAllTables($db_name);
        if($ext == 'sql'){
            exec('psql --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$db_name.' < '.$path.' 2>&1'  ,$output);
        }
        exec('pg_restore --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$db_name.' < '.$path.' 2>&1'  ,$output);
        Storage::delete($filename);
        return true;
    }

    protected function rollbackDatabase($restore_point_id) {
        $restore_point = DbBackup::with('Db')->find($restore_point_id);
        $success = $this->restoreDatabase($restore_point->Db,$restore_point->Db->name,$restore_point->filename);
        return redirect()->route('import_database',$restore_point->Db->name);
    }

    protected function importDatabaseFile($db_name) {

        $db_user = DbList::where('name', $db_name)->with('dbUser')->firstOrFail();
        $_db_users = [];
        foreach ($db_user->dbUser  as $key => $value) {
            $_db_users[$value->user_type] = $value;
        }
        $success = false;
        if (request()->isMethod('post')) {
            request()->validate(['file' => 'required|mimetypes:application/octet-stream,application/x-sql' ]); 
            $file =  request()->file('file');
            $ext = $file->getClientOriginalExtension();
            $this->dropAllTables($db_name);
            if($ext == 'sql'){
                exec('psql --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$db_name.' < '.$file.' 2>&1'  ,$output);
            }
            exec('pg_restore --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$db_name.' < '.$file.' 2>&1'  ,$output);
            $success = true;
            foreach ($_db_users  as $key => $value) {
                if ($value->user_type == 'readonly') {
                    $conn = $this->swicthDatabase($db_name);
                    $this->grantReadOnlyUserPrivileges($conn,$value->username); 
                    $this->closeTempConection();
                }else{
                    $conn = $this->swicthDatabase($db_name);
                    $conn->select("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO ".$value->username.";"); 
                    if($value->user_type == 'user'){
                        $this->changeDatabaseTablesOwner($conn,$value->username);
                    }
                    $this->closeTempConection();
                }
            }
        }
        return view('import_file', compact('db_name','success'));
    }

    protected function dropAllTables($db_name) {
        $tables = $this->selectAllTables($db_name);
        $conn = $this->swicthDatabase($db_name);
        if(count($tables)){
            $conn->select('DROP TABLE  "'.implode('","',$tables).'" CASCADE');
        }
        $this->closeTempConection();
    }

    protected function selectAllTables($db_name) {
        $conn = $this->swicthDatabase($db_name);
        $tables = ObjectToArray($conn->select("SELECT tablename FROM pg_tables WHERE schemaname='public'"));
        $this->closeTempConection();
        return array_column($tables,'tablename');
    }

    protected function backupDatabaseCron() {
        $db_name = 'openmind';
        $tables = $this->selectAllTables($db_name);
        $conn = $this->swicthDatabase($db_name);
        if(count($tables)){
            //dd('DROP TABLE  "'.implode('","',$tables).'" CASCADE');
        }
        $this->closeTempConection();
       abort(404);
    }

    protected function changeDatabaseTablesOwner($conn,$username){
        $commands = $conn->select("SELECT 'ALTER TABLE public.' || table_name || ' OWNER TO ".$username.";' as query from information_schema.tables where table_schema = 'public'"); 
        foreach ($commands as $key => $value) {
            $conn->statement($value->query);
        }
    }

    protected function backupInterval($db) {
        $dbdetails = DbList::findOrFail($db);
        if (request()->isMethod('post')) {
            request()->validate([ 
                'backp_time' => 'required|numeric|min:0|max:59',
                'backup_type' => 'required',
            ]);

            if(request()->backup_type){
                if(request()->backp_time > 23){
                    return Redirect::back()->withErrors(['backp_time' => 'In hourly backup interval must be betwwen 0 - 23'])->withInput(); 
                }
            }

            $dbdetails->backp_time = request()->backp_time;
            $dbdetails->backup_type = request()->backup_type;
            $dbdetails->save();
            return redirect()->route('db_details',$dbdetails->name); 
        }
        return view('db_interval', compact('db','dbdetails'));
    }

    public function getDbReports() {
        $db_reports_html = file_get_contents(public_path().'/db_logs.html');
        return view('db_reports', compact('db_reports_html'));     
    }

    public function deleteDatabaseBackup($db_name,$id) {
       DbBackup::find($id)->delete();
       return redirect()->route('import_database',$db_name);
    }

    public function downloadBackup($filename)
    {   
        $record = DbBackup::whereFilename($filename)->with('Db')->firstOrFail();
        $filepath = $record->Db->name.'/'.$filename;
        $mimetype =  Storage::disk('s3')->getDriver()->getMimetype($filepath); 
        $headers = [
          'Content-Type'        => $mimetype,            
          'Content-Disposition' => 'attachment; filename="'. $filename .'"',
        ];
        return \Response::make(Storage::disk('s3')->get($filepath), 200, $headers);
    }
}