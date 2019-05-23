@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:mx-auto lg:w-1/3">
            <div class="text-center text-xl bg-gray-200">Create Cluster</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('clusters.store') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3">Name</label>

                        <div class="flex flex-col flex-grow">
                            <input name="name" id="name" type="text"
                                   value="{{ old('name') }}"
                                   class="border rounded"
                                   required autofocus>

                            @if ($errors->has('name'))
                                <span class="text-red-800">{{ $errors->first('name') }}</span>
                            @endif
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
