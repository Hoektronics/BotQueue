<template>
  <div>
    <div :class="{ visible: !flow_supported, hidden: flow_supported}">
      Flow upload library not supported. We should probably handle this somehow later.
    </div>
    <div ref="flow_drop" class="visible"  :class="{ hidden: !flow_supported}">
      <p>
        <button ref="flow_browse" data-url="/foo">Upload</button>
        or drop here
      </p>
    </div>

    <div ref="progress_bar" class="relative pt-4" :class="{ visible: uploading, hidden: !uploading}">
      <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
        <div :style="{ width: progress + '%' }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
      </div>
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
      progress: 0,
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
    flow.on('filesSubmitted', function () {
      console.log("Uploading!");
      self.uploading = true;
      flow.upload();
    });
    flow.on('fileSuccess', function (file, message, chunk) {
      console.log("Success");
      location.href = chunk.xhr.responseURL;
    });
    flow.on('fileError', function (file, message) {
      console.log("Error!");
      console.log(message);
    });
    flow.on('fileProgress', function (file) {
      self.progress = (flow.progress() * 100).toFixed(0);
      console.log(`Progress! ${self.progress}`);
    });
  }
}
</script>

<style scoped>

</style>