@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border md:mx-auto md:w-1/3">
            <div class="text-center text-xl bg-gray-200">Reset Password</div>
            <div class="p-4">
                @if (session('status'))
                    <div class="p-2 border border-green-300 rounded bg-green-300 text-center">
                        {{ session('status') }}
                    </div>
                @else
                    <form role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="flex mb-3">
                            <label for="email" class="w-1/3">E-Mail Address</label>

                            <div class="flex flex-col flex-grow">
                                <input id="email" type="email" name="email" value="{{ old('email') }}"
                                       class="border rounded"
                                       required>

                                @if ($errors->has('email'))
                                    <span class="text-red-800">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn-blue btn-lg btn-interactive">
                                Send Password Reset Link
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
