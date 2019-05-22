@extends('layouts.app')

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.css"
          rel="stylesheet"/>
@endsection

@section('content')
    <div class="mx-4">
        <div class="flex justify-between">
            <span class="text-xl">Create Job</span>
            <a role="button"
               href="{{ route('jobs.file.store', $file) }}"
               class="btn-lg btn-blue btn-interactive"
               onclick="event.preventDefault(); document.getElementById('job-create-form').submit();">
                Create Job
            </a>
        </div>

        <form id="job-create-form" role="form" method="POST" action="{{ route('jobs.file.store', $file) }}">
            {{ csrf_field() }}

            <div class="flex w-2/3 mx-auto">
                <div class="w-1/3 m-2 rounded-lg border">
                    <div class="text-center text-xl bg-gray-200">Info</div>
                    <div class="p-4">
                        Creator: {{ $file->uploader->username }}
                    </div>
                </div>

                <div class="w-2/3 m-2 rounded-lg border">
                    <div class="flex bg-gray-200">
                        <div class="py-4 pl-4 pr-2">
                            <input type="checkbox" name="add_to_job"
                                   checked>
                        </div>

                        <input type="text"
                               value="{{ old('job_name', pathinfo($file->name, PATHINFO_FILENAME)) }}"
                               class="flex-grow m-2 p-2"
                               name="job_name">

                        @if ($errors->has('job_name'))
                            <span class="text-red-800">{{ $errors->first('job_name') }}</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex mb-3">
                            <label for="file_type" class="w-1/3">File Type</label>
                            <input type="text" class="flex-grow" id="file_type" value="{{ $file->type }}" disabled>
                        </div>

                        <div class="flex mb-3">
                            <label for="bot_cluster" class="w-1/3">Bot/Cluster</label>

                            <select name="bot_cluster" id="bot_cluster"
                                    class="flex-grow select-all">
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
                                <span class="w-1/3">{{ $errors->first('bot_cluster') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#bot_cluster').select2({
                theme: "classic"
            });
        });
    </script>
@endsection