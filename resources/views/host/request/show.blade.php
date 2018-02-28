@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="card w-50">
            <div class="card-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('hosts.store') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="host_request_id" value="{{ $host_request->id }}">

                    <div class="form-group">
                        <label for="name" class="control-label">Host Name</label>

                        <input name="name" type="text" value="{{ old('name', $host_request->hostname) }}"
                               id="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required autofocus>

                        @if ($errors->has('name'))
                            <span class="form-text">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>

                    @if($host_request->hostname !== null)
                        <div>
                            Device hostname: {{ $host_request->hostname }}
                        </div>
                    @endif

                    @if($host_request->local_ip !== null)
                        <div>
                            Local IP: {{ $host_request->local_ip }}
                        </div>
                    @endif

                    @unless($host_request->local_ip === null && $host_request->hostname === null)
                        <br>
                    @endif

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            Claim Host
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection