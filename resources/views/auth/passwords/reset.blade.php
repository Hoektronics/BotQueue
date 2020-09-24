@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:mx-auto lg:w-1/3">
            <div class="text-center text-xl bg-gray-200">Reset Password</div>

            <div class="p-4">
                <form role="form" method="POST" action="{{ route('password.request') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="flex mb-3 items-center">
                        <label for="email" class="w-1/3 my-auto">E-Mail Address</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('email'))
                                <span class="input-error">{{ $errors->first('email') }}</span>
                            @endif

                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}"
                                   class="input"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="flex mb-3 items-center">
                        <label for="password" class="w-1/3 my-auto">Password</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('password'))
                                <span class="input-error">{{ $errors->first('password') }}</span>
                            @endif

                            <input id="password" type="password" name="password"
                                   class="input"
                                   required>
                        </div>
                    </div>

                    <div class="flex mb-3 items-center">
                        <label for="password-confirm" class="w-1/3 my-auto">Confirm Password</label>

                        <input id="password-confirm" type="password" name="password_confirmation"
                               class="flex-grow input"
                               required>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
