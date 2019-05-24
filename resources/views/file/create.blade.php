@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
            <div class="text-center text-xl bg-gray-200">Upload a file</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="input-with-error mb-3">
                        @if ($errors->has('file'))
                            <span class="input-error">{{ $errors->first('file') }}</span>
                        @endif

                        <div class="flex border border-blue-500 rounded position-relative inline-block mb-0 overflow-hidden cursor-pointer input">
                            <input id="file" class="flex-none m-0 opacity-0 w-0" name="file" type="file">
                            <label class="flex flex-grow cursor-pointer file-label" for="file">
                                <span id="file-label" class="flex-grow my-auto p-2 overflow-hidden"></span>
                                <span class="bg-blue-500 p-2 flex flex-col choose-file">
                                    <span class="my-auto text-white text-xl whitespace-no-wrap choose-file">Choose file</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-blue btn-lg btn-interactive">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // We can attach the `fileselect` event to all file inputs on the page
        $(document).on('change', ':file', function () {
            var input = $(this);
            var numFiles = input.get(0).files ? input.get(0).files.length : 1;
            var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        // We can watch for our custom `fileselect` event like this
        $(document).ready(function () {
            $(':file').on('fileselect', function (event, numFiles, label) {
                var input = $('#file-label');
                var log = numFiles > 1 ? numFiles + ' files selected' : label;

                input.text(log);
            });
        });
    </script>
@endsection