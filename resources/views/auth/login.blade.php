@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Login</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="username" class="w-1/3 my-auto">Username</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('username'))
                                <span class="input-error">{{ $errors->first('username') }}</span>
                            @endif

                            <input id="username" type="text" name="username"
                                   value="{{ old('username') }}"
                                   class="input"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="flex my-3">
                        <label for="password" class="w-1/3 my-auto">Password</label>

                        <input id="password" type="password" name="password" required
                               class="flex-grow input">
                    </div>

                    <div class="flex my-3">
                        <label for="remember" class="w-1/3 my-auto">Remember Me</label>
                        <div class="flex-grow flex justify-end">
                            <input type="checkbox" id="remember" name="remember"
                                   class="my-auto border rounded"
                                    {{ old('remember') ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="flex mt-4">
                        <div class="flex-grow mt-auto justify-start">
                            <a class="text-sm align-text-bottom text-gray-600"
                               href="{{ route('password.request') }}">
                                Forgot Your Password?
                            </a>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-blue btn-lg btn-interactive">
                                Login
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
