@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
          
            <div class="card">
                <div class="card-header">{{ __('Set Backup Interval') }}</div>

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('backup_interval',$db) }}">
                        @csrf

                         <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Backup type') }}</label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <input id="type_m" {{ ($dbdetails->backup_type == 0) ? 'checked' : '' }}  type="radio" required class="{{ $errors->has('backup_type') ? ' is-invalid' : '' }}" name="backup_type" value="0" autofocus> 
                                    <label for="type_m" class=" col-form-label text-md-right">Minutes</label>

                                    <input id="type_h" {{ ($dbdetails->backup_type == 1) ? 'checked' : '' }} type="radio" class="{{ $errors->has('backup_type') ? ' is-invalid' : '' }}" name="backup_type" value="1"> 
                                    <label for="type_h" class=" col-form-label text-md-right">Hourly</label>
                                </div>
                                @if ($errors->has('backup_type'))
                                    <span class="invalid-feedback" style="display:block;" role="alert">
                                        <strong>{{ $errors->first('backup_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Interval') }}</label>

                            <div class="col-md-6">
                                <input id="backp_time" type="number" required min="0" max="59" class="form-control{{ $errors->has('backp_time') ? ' is-invalid' : '' }}" name="backp_time" value="{{ $dbdetails->backp_time }}" autofocus>
                                <span class="form-text text-muted">0 to stop the autometic backup</span>
                                @if ($errors->has('backp_time'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('backp_time') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
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
