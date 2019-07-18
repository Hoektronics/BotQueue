@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:mx-auto lg:w-1/3">
            <div class="text-center text-xl bg-gray-200">Create Cluster</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('clusters.store') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3 my-auto">Name</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('name'))
                                <span class="input-error">{{ $errors->first('name') }}</span>
                            @endif

                            <input name="name" id="name" type="text"
                                   value="{{ old('name') }}"
                                   class="input"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Create Cluster
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection