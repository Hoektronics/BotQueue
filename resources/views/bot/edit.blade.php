@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Edit Bot</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('bots.update', [$bot]) }}">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3">Name</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('name'))
                                <span class="input-error">{{ $errors->first('name') }}</span>
                            @endif

                            <input id="name" type="text" name="name"
                                   value="{{ old('name', $bot->name) }}"
                                   class="input"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="flex mt-4 justify-end">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection