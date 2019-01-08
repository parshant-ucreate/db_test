@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <ul class="list-group">
                        @foreach($database_list as $database)
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{$database->name}}
                            <span class="badge badge-primary">{{$database->db_size}}</span>
                            <a href="{{url('/drop_database')}}"><span class="badge badge-danger">X</span></a>
                          </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
