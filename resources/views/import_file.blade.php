@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($success)
                <div class="alert alert-success alert-dismissable">
                    Databse Restore Successfuly! 
                </div>
             @endif
            <div class="card">
                <div class="card-header">{{ __('Restore Database') }}
                    <a href="{{ route('import_database',$db_name)}}" style="float: right;">{{ __('Backup Url') }}</a>
                </div>

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('import_file',$db_name) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="file" class="col-md-4 col-form-label text-md-right">{{ __('Backup File') }}</label>

                            <div class="col-md-6">
                                <input id="file" type="file" accept=".sql,.dump" class="form-control{{ $errors->has('file') ? ' is-invalid' : '' }}" name="file" value="" required autofocus>

                                @if ($errors->has('file'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('file') }}</strong>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
