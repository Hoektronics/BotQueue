@extends('layouts.app')

@section('content')
    <h1>Hosts</h1>

    @if(count($hosts) == 0)
        <h4>
            No hosts available. See help to add one (TODO)
        </h4>
    @else
        @foreach($hosts as $host)
            {{--<a href="{{ route('hosts.show', [$host]) }}">{{ $host->name }}</a>--}}
            <a>{{ $host->name }}</a><br>
        @endforeach
    @endif
@endsection