@extends('layouts.app')

@section('content')
    <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
        <div class="text-center text-xl bg-gray-200">Upload a file</div>
        <div class="p-4">
            <div x-data="alpine_file_upload()" x-init="init()">
                <div x-show="!flow_supported">
                    Flow upload library not supported. We should probably handle this somehow later.
                </div>
                <div x-ref="flow_drop" class="visible text-center" x-show="show_upload()">
                    <p>
                        <button x-ref="flow_browse"
                                class="p-2 shadow border rounded bg-blue-400 border-blue-400 text-white hover:bg-blue-600">
                            Upload
                        </button>
                        or drop here
                    </p>
                </div>

                <div class="relative" x-show="show_progress()">
                    <div class="flex mb-2 items-center justify-between">
                        <span x-text="file_name" class="overflow-hidden whitespace-no-wrap mr-4 truncate"></span>
                        <div class="text-right">
                            <span x-text="progress + '%'" class="text-green-500"></span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
                        <div x-bind:style="progress_style()"
                             class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                    </div>
                </div>

                <div class="text-center border-red-600 border rounded p-1 text-red-600 text-xl"
                     x-show="has_error()"
                     x-text="error"
                     x-bind:class="{ 'mt-4' : show_upload() }">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function alpine_file_upload() {
            return {
                flow_supported: true,
                uploading: false,
                file_name: "",
                progress: 0,
                error: null,
                flow: null,
                show_upload: function () {
                    return this.flow_supported && !this.uploading;
                },
                show_progress: function () {
                    return this.flow_supported && this.uploading && !this.has_error();
                },
                has_error: function () {
                    return this.error != null;
                },
                progress_style: function () {
                    return "width: " + this.progress + "%";
                },
                init: function () {
                    let token = document.querySelector('meta[name="csrf-token"]').content;
                    this.flow = new Flow({
                        chunkSize: 1000 * 1000, // 1MB ish
                        forceChunkSize: true,
                        simultaneousUploads: 1,
                        testChunks: false,
                        // Get the url from data-url tag
                        target: "/files",
                        // Append token to the request - required for web routes
                        query: {_token: token}
                    });

                    if (!this.flow.support) {
                        this.flow_supported = false;
                        return;
                    }

                    this.flow.assignDrop(this.$refs.flow_drop);
                    this.flow.assignBrowse(this.$refs.flow_browse, false, true, {});

                    let self = this;
                    this.flow.on('fileAdded', function (file, event) {
                        self.error = null;
                        self.uploading = false;
                    });
                    this.flow.on('filesSubmitted', function (files, event) {
                        self.file_name = files[0].name;
                        self.uploading = true;
                        self.flow.upload();
                    });
                    this.flow.on('fileSuccess', function (file, message, chunk) {
                        location.href = chunk.xhr.responseURL;
                    });
                    this.flow.on('fileError', function (file, message) {
                        self.uploading = false
                        try {
                            message = JSON.parse(message);

                            if (message.hasOwnProperty('error')) {
                                self.error = message.error;
                            }
                        } catch (e) {
                        }

                        if (self.error === null) {
                            self.error = "Oh no, something went wrong!"
                        }

                        console.log("File upload error!");
                        console.log(message);
                    });
                    this.flow.on('fileProgress', function (file) {
                        self.progress = (self.flow.progress() * 100).toFixed(0);
                    });
                }
            }
        }
    </script>
@endsection