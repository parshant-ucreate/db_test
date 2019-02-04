@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if ($success)
                <div class="alert alert-success alert-dismissable">
                    Databse Restore Successfuly! 
                </div>
             @endif
            <div class="card">
                <div class="card-header">{{ __('Restore Database') }}
                    <a href="{{ route('import_file',$db_name)}}" style="float: right;">{{ __('Backup File') }}</a>
                </div>

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('import_database',$db_name) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('Backup Url') }}</label>

                            <div class="col-md-6">
                                <input id="url" type="url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" name="url" value="" required autofocus>

                                @if ($errors->has('url'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Restore Database') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#home">Backups History</a></li>
                        <li><a data-toggle="tab" href="#menu">Retore Points</a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active show">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($database->dbBackup as $k => $val)
                                        @if($val->type !='restore')
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $val->filename }}</td>
                                                <td>{{ ucfirst($val->type) }}</td>
                                                <td>{{ $val->created_at }}</td>
                                                <td>
                                                    <form style="float:left;padding-right:3px" method="POST" action="{{url('/'.$db_name.'/import/'.$val->id)}}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete?')" type="submit">Delete</button>
                                                    </form>
                                                    <a class="btn btn-sm btn-primary" href="{{ url('/download_backup/'.$val->filename) }}">Download</a>
                                                    <button class="btn btn-sm btn-default" type="button" onclick="copy_url('{{ $val->filename }}')" >Copy Url</button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div id="menu" class="tab-pane fade">
                            <ul class="list-group">
                                @foreach($database->dbRestorePoints as $k => $val)
                                    <li class="list-group-item">Last restore atempt on {{ $val->created_at }}
                                        <a href="{{ route('rollback_database',[$val->restore_point_id]) }}" class="" onclick="return confirm('Are you sure you want to rollback to this point?')" >Rollback here</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function copy_url(file) {
        var bucket_url = '{!! env('BUCKET_URL').$db_name.'/' !!}' + file;
        $('#url').val(bucket_url);
    }
</script>
@endsection