@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="mx-auto w-1/3 rounded-lg border">
            <div class="text-center text-xl bg-gray-200">Create Bot</div>
            <div class="p-4 border-green-500">
                <form role="form" method="POST" action="{{ route('bots.store') }}">
                    {{ csrf_field() }}

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3">Name</label>

                        <input name="name" id="name" type="text"
                               value="{{ old('name') }}"
                               class="flex-grow my-auto border rounded"
                               required autofocus>

                        @if ($errors->has('name'))
                            <span>
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="flex mb-3">
                        <label for="type" class="w-1/3">Bot Type</label>

                        <select name="type" id="type"
                        class="flex-grow my-auto border rounded">
                            <option value="3d_printer">3D Printer</option>
                        </select>

                        @if ($errors->has('type'))
                            <span>
                                <strong>{{ $errors->first('type') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="flex mb-3">
                        <label for="cluster" class="w-1/3">Cluster</label>

                        <select name="cluster" id="cluster"
                        class="flex-grow my-auto border rounded">
                            @foreach($clusters as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('cluster'))
                            <span>
                                <strong>{{ $errors->first('cluster') }}</strong>
                            </span>
                        @endif
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
