@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Create Bot</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('bots.store') }}">
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

                    <div class="flex mb-3">
                        <label for="type" class="w-1/3 my-auto">Bot Type</label>

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
                        <label for="cluster" class="w-1/3 my-auto">Cluster</label>

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
