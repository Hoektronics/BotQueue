@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:mx-auto lg:w-1/3">
            <div class="text-center text-xl bg-gray-200">Create Cluster</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('clusters.store') }}">
                    @csrf

                    <x-input.text
                            name="name"
                            label="Name"
                            class="mb-3"
                            foo="bar"
                            required autofocus
                    ></x-input.text>

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
