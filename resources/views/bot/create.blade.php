@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-4 w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Create Bot</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('bots.store') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3">Name</label>

                        <div class="flex flex-col flex-grow">
                            <input name="name" id="name" type="text"
                                   value="{{ old('name') }}"
                                   class="my-auto border rounded"
                                   required autofocus>

                            @if ($errors->has('name'))
                                <span class="text-red-800">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex mb-3">
                        <label for="type" class="w-1/3">Bot Type</label>

                        <div class="flex flex-col flex-grow">
                            <select name="type" id="type"
                            class="my-auto border rounded">
                                <option value="3d_printer">3D Printer</option>
                            </select>

                            @if ($errors->has('type'))
                                <span class="text-red-800">{{ $errors->first('type') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex mb-3">
                        <label for="cluster" class="w-1/3">Cluster</label>

                        <div class="flex flex-col flex-grow">
                            <select name="cluster" id="cluster"
                            class="my-auto border rounded">
                                @foreach($clusters as $cluster)
                                    <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                                @endforeach
                            </select>

                            @if ($errors->has('cluster'))
                                <span class="text-red-800">{{ $errors->first('cluster') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg">
                            Create Bot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
