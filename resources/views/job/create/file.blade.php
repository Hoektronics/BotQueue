@extends('layouts.app')

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.css"
          rel="stylesheet"/>
@endsection

@section('content')
    <div class="btn-toolbar float-right">
        <a role="button"
           class="btn btn-primary btn-lg"
           href="{{ route('jobs.file.store', $file) }}"
           onclick="event.preventDefault(); document.getElementById('job-create-form').submit();">
            Create Job
        </a>
    </div>
    <h1>Create Job</h1>

    <form class="form-horizontal" id="job-create-form" role="form" method="POST" action="{{ route('jobs.file.store', $file) }}">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        Info
                    </div>
                    <div class="card-body">
                        Creator: {{ $file->uploader->username }}
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="add_to_job" checked>
                                </div>
                            </div>

                            <input type="text" value="{{ old('job_name', pathinfo($file->name, PATHINFO_FILENAME)) }}"
                                   name="job_name" class="form-control{{ $errors->has('job_name') ? ' is-invalid' : '' }}">
                        </div>

                        @if ($errors->has('job_name'))
                            <span class="form-text">
                                <strong>{{ $errors->first('job_name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label for="file_type" class="control-label">File Type</label>
                            <input type="text" id="file_type" class="form-control{{ $errors->has('file_type') ? ' is-invalid' : '' }}" value="{{ $file->type }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="bot_cluster" class="control-label">Bot/Cluster</label>

                            <select name="bot_cluster" id="bot_cluster" class="form-control select{{ $errors->has('bot_cluster') ? ' is-invalid' : '' }}">
                                @if(count($clusters) > 0)
                                    <optgroup label="Clusters">
                                        @foreach($clusters as $cluster)
                                            <option value="clusters_{{ $cluster->id }}"
                                                @if(old('bot_cluster') == "clusters_".$cluster->id) selected @endif
                                            >
                                                {{ $cluster->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif


                                @if(count($bots) > 0)
                                    <optgroup label="Bots">
                                        @foreach($bots as $bot)
                                            <option value="bots_{{ $bot->id }}"
                                                @if(old('bot_cluster') == "bots_".$bot->id) selected @endif
                                            >
                                                {{ $bot->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>

                            @if ($errors->has('bot_cluster'))
                                <span class="form-text">
                                    <strong>{{ $errors->first('bot_cluster') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script type="text/javascript">
        $('.select').select2({
            theme: "bootstrap"
        });
    </script>
@endsection