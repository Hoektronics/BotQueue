@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Create Bot</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('bots.store') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3">Name</label>

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

                    <div class="flex mb-3">
                        <label for="type" class="w-1/3">Bot Type</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('type'))
                                <span class="input-error">{{ $errors->first('type') }}</span>
                            @endif

                            <select name="type" id="type"
                                    class="input">
                                <option value="3d_printer">3D Printer</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex mb-3">
                        <label for="cluster" class="w-1/3">Cluster</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('cluster'))
                                <span class="input-error">{{ $errors->first('cluster') }}</span>
                            @endif

                            <select name="cluster" id="cluster"
                                    class="input appearance-none">
                                @foreach($clusters as $cluster)
                                    <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                                @endforeach
                            </select>

{{--                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" height="1rem"><path d="M1 4h2v2H1V4zm4 0h14v2H5V4zM1 9h2v2H1V9zm4 0h14v2H5V9zm-4 5h2v2H1v-2zm4 0h14v2H5v-2z"></path></svg>--}}
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Create Bot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
