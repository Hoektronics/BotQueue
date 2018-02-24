@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="card w-50">
            <div class="card-header">Create Bot</div>
            <div class="card-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('bots.store') }}">
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
                        <label for="type" class="control-label">Bot Type</label>

                        <select name="type" id="type" class="form-control{{ $errors->has('type') ? ' is-invalid' : '' }}">
                            <option value="3d_printer">3D Printer</option>
                        </select>

                        @if ($errors->has('type'))
                            <span class="form-text">
                                <strong>{{ $errors->first('type') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="cluster" class="control-label">Cluster</label>

                        <select name="cluster" id="cluster" class="form-control{{ $errors->has('cluster') ? ' is-invalid' : '' }}">
                            @foreach($clusters as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('cluster'))
                            <span class="help-block">
                                <strong>{{ $errors->first('cluster') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            Create Bot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
