@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:mx-auto lg:w-1/3">
            <div class="text-center text-xl bg-gray-200">Reset Password</div>

            <div class="p-4">
                <form role="form" method="POST" action="{{ route('password.request') }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="flex mb-3">
                        <label for="email" class="w-1/3">E-Mail Address</label>

                        <div class="flex flex-col flex-grow">
                            <input id="email" type="email" name="email" value="{{ $email or old('email') }}"
                                   class="border rounded"
                                   required autofocus>

                            @if ($errors->has('email'))
                                <span class="text-red-800">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex mb-3">
                        <label for="password" class="w-1/3">Password</label>

                        <div class="flex flex-col flex-grow">
                            <input id="password" type="password" name="password"
                                   class="border rounded"
                                   required>

                            @if ($errors->has('password'))
                                <span class="text-red-800">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex mb-3">
                        <label for="password-confirm" class="w-1/3">Confirm Password</label>

                        <div class="flex flex-col flex-grow">
                            <input id="password-confirm" type="password" name="password_confirmation"
                                   class="border rounded"
                                   required>
                        </div>
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
