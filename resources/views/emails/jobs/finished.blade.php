@extends('layouts.email')

@section('content')
    We're just writing to let you know that your job, "{{ $job->name }}" is finished!<br>

    <a href="{{ route('jobs.show', [$job]) }}">Click here</a> to let us know if this job failed or succeeded so your bot can move on to the next job!<br>
@endsection