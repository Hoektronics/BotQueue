@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="card w-50">
            <div class="card-header">Create Cluster</div>
            <div class="card-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('clusters.store') }}">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="name" class="control-label">Name</label>

                        <input name="name" id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                               value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('name'))
                            <span class="form-text">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            Create Cluster
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
