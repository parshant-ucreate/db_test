@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Db Name : {{$db_name}}</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($db_user->dbUser as $user)
                          <li class="list-group-item justify-content-between align-items-center">
                          <p><span>Username : </span>{{$user->username}}</p>
                          <p><span>Password : </span>{{$user->password}}</p>
                          <p><span>UserType : </span>{{ucfirst($user->user_type)}}</p>
                            
                          </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-footer">
                  <a class="btn btn-primary" href="{{ route('backup_database' , $db_name ) }}">Download dump</a>
                  <a class="btn btn-primary" href="{{ route('import_database' , $db_name ) }}">Restore database</a>
                  <a class="btn btn-primary" href="{{ route('backup_interval' , $db_user->id ) }}">Backup Interval</a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
