@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Claim Host</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('hosts.store') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="host_request_id" value="{{ $host_request->id }}">

                    <div class="flex mb-3">
                        <label for="name" class="w-1/3 my-auto">Host Name</label>

                        <div class="input-with-error flex-grow">
                            @if ($errors->has('name'))
                                <span class="input-error">{{ $errors->first('name') }}</span>
                            @endif

                            <input name="name" id="name" type="text"
                                   value="{{ old('name', $host_request->hostname) }}"
                                   class="input"
                                   required autofocus>
                        </div>
                    </div>

                    @if($host_request->hostname !== null)
                        <div class="flex mb-3">
                            <label for="hostname" class="w-1/3 my-auto">Device hostname</label>
                            <input type="text" class="flex-grow" id="hostname" value="{{ $host_request->hostname }}" disabled>
                        </div>
                    @endif

                    @if($host_request->local_ip !== null)
                        <div class="flex mb-3">
                            <label for="local_ip" class="w-1/3 my-auto">Local IP</label>
                            <input type="text" class="flex-grow" id="local_ip" value="{{ $host_request->local_ip }}" disabled>
                        </div>
                    @endif

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Claim Host
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection