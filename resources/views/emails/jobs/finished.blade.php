@extends('layouts.email')

@section('content')
    We're just writing to let you know that your job, "{{ $job->name }}" is finished!<br>

    <a href="{{ route('jobs.show', [$job]) }}">Click here</a> to let us know if this job failed or succeeded so your bot can move on to the next job!<br>

    <div class="flex justify-content-center">
        <form method="post" action="/jobs/{{$job->id}}/pass">
            {{ csrf_field() }}
            <input type="submit" class="btn-green btn-lg btn-interactive m-2" value="Pass">
        </form>

        <form method="post" action="/jobs/{{$job->id}}/fail">
            {{ csrf_field() }}
            <input type="submit" class="btn-red btn-lg btn-interactive m-2" value="Fail">
        </form>
    </div>
@endsection