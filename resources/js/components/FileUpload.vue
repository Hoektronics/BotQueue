<template>
  <div>
    <div :class="{ visible : !resumable_supported, hidden : resumable_supported}">
      Resumable not supported. We should probably handle this somehow later.
    </div>
    <div ref="resumable_drop" :class="{ visible : resumable_supported, hidden : !resumable_supported}">
      <p>
        <button ref="resumable_browse" data-url="/foo">Upload</button>
        or drop here
      </p>
      <p>Uses `api/upload` endpoint which uses `browser` data instead of session (session is not inited in api routes).
        This is automatically detected.</p>
    </div>
    <ul id="file-upload-list" class="list-unstyled hidden">

    </ul>
  </div>
</template>

<script>
const Resumable = require('resumablejs');

export default {
  name: "FileUpload",
  props: ['token'],
  data: function () {
    return {
      resumable_supported: true
    }
  },
  mounted() {
    const resumable = new Resumable({
      chunkSize: 1000 * 1000, // 1MB ish
      forceChunkSize: true,
      simultaneousUploads: 3,
      testChunks: false,
      throttleProgressCallbacks: 1,
      // Get the url from data-url tag
      target: "/upload-advanced",
      // Append token to the request - required for web routes
      query: {_token: this.token}
    });

    if(!resumable.support) {
      this.resumable_supported = false;
      return;
    }

    resumable.assignDrop(this.$refs.resumable_drop);
    resumable.assignBrowse(this.$refs.resumable_drop, false);

    resumable.on('fileAdded', function (file) {
      console.log("Uploading!");
      resumable.upload();
    });
    resumable.on('fileSuccess', function (file, message) {
      console.log("Success");
    });
    resumable.on('fileError', function (file, message) {
      console.log("Error!");
      console.log(message);
    });
    resumable.on('fileProgress', function (file) {
      console.log(`Progress! ${(resumable.progress() * 100).toFixed(2)}`);
    });
  }
}
</script>

<style scoped>

</style>