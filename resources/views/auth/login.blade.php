@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="card w-50">
            <div class="card-header">Login</div>
            <div class="card-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="username" class="control-label">Username</label>

                        <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username"
                               value="{{ old('username') }}" required autofocus>

                        @if ($errors->has('username'))
                            <span class="form-text">
                                <strong>{{ $errors->first('username') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>

                        <input id="password" type="password" class="form-control" name="password" required>

                        @if ($errors->has('password'))
                            <span class="form-text">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            Login
                        </button>

                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            Forgot Your Password?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
