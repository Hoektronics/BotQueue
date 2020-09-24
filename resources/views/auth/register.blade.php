@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Register</div>
            @if(setting('registration.enabled'))
                <div class="p-4">
                    <form role="form" method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="flex mb-3 items-center">
                            <label for="username" class="w-1/3 my-auto">Username</label>

                            <div class="input-with-error flex-grow">
                                @error('username')
                                <span class="input-error">{{ $message }}</span>
                                @enderror

                                <input id="username" type="text" name="username" value="{{ old('username') }}" required
                                       class="input"
                                       autofocus>
                            </div>
                        </div>

                        <div class="flex my-3">
                            <label for="email" class="w-1/3 my-auto">E-Mail Address</label>

                            <div class="input-with-error flex-grow">
                                @error('email')
                                    <span class="input-error">{{ $message }}</span>
                                @enderror

                                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                       class="input">
                            </div>
                        </div>

                        <div class="flex my-3">
                            <label for="password" class="w-1/3 my-auto">Password</label>

                            <div class="input-with-error flex-grow">
                                @error('password')
                                    <span class="input-error">{{ $message }}</span>
                                @enderror

                                <input id="password" type="password" name="password" required
                                       class="input">
                            </div>
                        </div>

                        <div class="flex my-3">
                            <label for="password-confirm" class="w-1/3 my-auto">Confirm Password</label>

                            <input id="password-confirm" type="password" name="password_confirmation" required
                                   class="flex-grow input">
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn-blue btn-lg btn-interactive">
                                Register
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-4 text-center">
                    <span>Registration is disabled for this site.</span>
                </div>
            @endif
        </div>
    </div>
@endsection
