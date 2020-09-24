@extends('layouts.app')

@section('content')
    <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
        <div class="text-center text-xl bg-gray-200">Login</div>
        <div class="p-4">
            <form role="form" method="POST" action="{{ route('login') }}">
                @csrf

                <x-input.text
                        name="username"
                        label="Username"
                        class="mb-3"
                        required autofocus
                ></x-input.text>

                <x-input.text
                        name="password"
                        label="Password"
                        type="password"
                        class="mb-3"
                        required
                ></x-input.text>

                <div class="flex mb-3">
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
@endsection
