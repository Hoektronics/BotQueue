<template>
  <div>
    <div :class="{ visible: !flow_supported, hidden: flow_supported}">
      Flow upload library not supported. We should probably handle this somehow later.
    </div>
    <div ref="flow_drop" class="visible text-center" :class="{ hidden: !this.show_upload }">
      <p>
        <button ref="flow_browse"
                class="p-2 shadow border rounded bg-blue-400 border-blue-400 text-white hover:bg-blue-600">
          Upload
        </button>
        or drop here
      </p>
    </div>

    <div class="relative" :class="{ visible: show_progress, hidden: !show_progress}">
      <div class="flex mb-2 items-center justify-between">
        <span class="overflow-hidden whitespace-no-wrap mr-4 truncate">
          {{ file_name }}
        </span>
        <div class="text-right">
          <span class="text-green-500">
            {{ progress }}%
          </span>
        </div>
      </div>
      <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
        <div :style="{ width: progress + '%' }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
      </div>
    </div>

    <div class="text-center border-red-600 border rounded p-1 text-red-600 text-xl" :class="{ visible: error, hidden: !error }">
      Oh no, something went wrong!
    </div>
  </div>
</template>

<script>
const Flow = require('@flowjs/flow.js');

export default {
  name: "FileUpload",
  props: ['token'],
  data: function () {
    return {
      flow_supported: true,
      uploading: false,
      error: false,
      progress: 0,
      file_name: "",
    }
  },
  computed: {
    show_upload: function() {
      return this.flow_supported && !this.uploading && !this.error;
    },
    show_progress: function() {
      return this.flow_supported && this.uploading && !this.error;
    }
  },
  mounted() {
    const flow = new Flow({
      chunkSize: 1000 * 1000, // 1MB ish
      forceChunkSize: true,
      simultaneousUploads: 3,
      testChunks: false,
      // Get the url from data-url tag
      target: "/upload-advanced",
      // Append token to the request - required for web routes
      query: {_token: this.token}
    });
    window.flow = flow;

    if(!flow.support) {
      this.flow_supported = false;
      return;
    }

    flow.assignDrop(this.$refs.flow_drop);
    flow.assignBrowse(this.$refs.flow_browse, false, true, {});

    let self = this;
    flow.on('filesSubmitted', function (files, event) {
      self.file_name = files[0].name;
      self.uploading = true;
      flow.upload();
    });
    flow.on('fileSuccess', function (file, message, chunk) {
      location.href = chunk.xhr.responseURL;
    });
    flow.on('fileError', function (file, message) {
      self.error = true;
      console.log("File upload error!");
      console.log(message);
    });
    flow.on('fileProgress', function (file) {
      self.progress = (flow.progress() * 100).toFixed(0);
    });
  }
}
</script>

<style scoped>

</style>