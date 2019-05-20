@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-auto w-1/3 rounded-lg border">
            <div class="text-center text-xl bg-gray-200">Login</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="username" class="w-1/3">Username</label>

                        <div class="flex flex-col flex-grow">
                            <input id="username" type="text" name="username"
                                   value="{{ old('username') }}"
                                   class="border rounded"
                                   required autofocus>

                            @if ($errors->has('username'))
                                <span class="text-red-800">{{ $errors->first('username') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex my-3">
                        <label for="password" class="w-1/3">Password</label>

                        <input id="password" type="password" name="password" required
                        class="flex-grow border rounded">
                    </div>

                    <div class="flex my-3">
                        <label for="remember" class="w-1/3">Remember Me</label>
                        <input type="checkbox" id="remember" name="remember"
                               class="flex-grow my-auto border rounded"
                                {{ old('remember') ? 'checked' : '' }}>
                    </div>

                    <div class="flex">
                        <a class="text-sm align-text-bottom mt-auto text-gray-600 flex-grow"
                                href="{{ route('password.request') }}">
                            Forgot Your Password?
                        </a>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn-blue">
                                Login
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
