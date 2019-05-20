@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border md:w-1/3 md:mx-auto">
            <div class="text-center text-xl bg-gray-200">Register</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('register') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="username" class="w-1/3">Username</label>

                        <div class="flex flex-col flex-grow">
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                               class="border rounded"
                               autofocus>

                            @if ($errors->has('username'))
                                <span class="text-red-800">{{ $errors->first('username') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex my-3">
                        <label for="email" class="w-1/3">E-Mail Address</label>

                        <div class="flex flex-col flex-grow">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               class="border rounded">

                            @if ($errors->has('email'))
                                <span class="text-red-800">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex my-3">
                        <label for="password" class="w-1/3">Password</label>

                        <div class="flex flex-col flex-grow">
                        <input id="password" type="password" name="password" required
                               class="border rounded">

                            @if ($errors->has('password'))
                                <span class="text-red-800">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex my-3">
                        <label for="password-confirm" class="w-1/3">Confirm Password</label>

                        <input id="password-confirm" type="password" name="password_confirmation" required
                               class="flex-grow border rounded">
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
