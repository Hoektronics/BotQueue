@extends('layouts.app')

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.css"
          rel="stylesheet"/>
@endsection

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button"
               class="btn btn-primary btn-lg"
               href="{{ route('job.file.store') }}"
               onclick="event.preventDefault(); document.getElementById('job-create-form').submit();">
                Create Job
            </a>
        </div>
        <h1>Create Job</h1>
    </div>
    <form class="form-horizontal" id="job-create-form" role="form" method="POST" action="{{ route('job.file.store') }}">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Info
                    </div>
                    <div class="panel-body">
                        Creator: {{ $file->uploader->username }}
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" name="add_to_job" checked>
                            </span>
                                <input type="text" value="{{ pathinfo($file->name, PATHINFO_FILENAME) }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="form-group{{ $errors->has('bot_cluster') ? ' has-error' : '' }}">
                                <label for="file_type" class="col-md-2 control-label">File Type</label>
                                <div class="col-md-10">
                                    <input type="text" id="file_type" class="form-control" value="{{ $file->type }}" disabled>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('bot_cluster') ? ' has-error' : '' }}">
                                <label for="bot_cluster" class="col-md-2 control-label">Bot/Cluster</label>
                                <div class="col-md-10">
                                    <select name="bot_cluster" id="bot_cluster" class="form-control select">
                                        @if(count($clusters) > 0)
                                            <optgroup label="Clusters">
                                                @foreach($clusters as $cluster)
                                                    <option value="bots_{{ $cluster->id }}">{{ $cluster->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif


                                        @if(count($bots) > 0)
                                            <optgroup label="Bots">
                                                @foreach($bots as $bot)
                                                    <option value="clusters_{{ $bot->id }}">{{ $bot->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>

                                    @if ($errors->has('bot_cluster'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bot_cluster') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($file->type == \App\Enums\FileTypeEnum::STL)
                        <div class="panel panel-default panel-indent">
                            <div class="panel-heading">
                                Slice!
                            </div>
                        </div>
                    @endif
                    @if(in_array($file->type, [\App\Enums\FileTypeEnum::STL, \App\Enums\FileTypeEnum::GCode]))
                        <div class="panel panel-default panel-indent">
                            <div class="panel-heading">
                                Print!
                            </div>
                        </div>
                    @endif
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