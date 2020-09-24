@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Register</div>
            @if(setting('registration.enabled'))
                <div class="p-4">
                    <form role="form" method="POST" action="{{ route('register') }}">
                        @csrf

                        <x-input.text
                                name="username"
                                label="Username"
                                class="mb-3"
                                required autofocus
                        ></x-input.text>

                        <x-input.text
                                name="email"
                                label="E-mail Address"
                                type="email"
                                class="mb-3"
                                required
                        ></x-input.text>

                        <x-input.text
                                name="password"
                                label="Password"
                                type="password"
                                class="mb-3"
                                required
                        ></x-input.text>

                        <x-input.text
                                name="password_confirmation"
                                label="Confirm Password"
                                type="password"
                                class="mb-3"
                                required
                        ></x-input.text>

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
