<div class="row">
	<div class="span6">
		<h2>How it works:</h2>
		<ol>
			<li>You upload a 3D Model to our server.</li>
			<li>You specify the queue and # of prints you want.</li>
			<li>The client software downloads, slices, and executes each job.</li>
			<li>You take your shiny new stuff and do awesomeness.</li>
		</ol>
		
		<h2>Accepted filetypes:</h2>
		<ul>
		  <li><strong>.gcode</strong> - will be executed directly with no extra processing.</li>
		  <li><strong>.stl / .obj / .amf</strong> - will be sliced and executed based on your config.</li>
		  <li><strong>.zip</strong> - usable files will be extracted and added to your queue.</li>
		</ul>
	</div>
	<div class="span6">
	  <h2>Option 1: Upload a File</h2>
		<?= Controller::byName('upload')->renderView('uploader', array(
			'payload' => array(
			'type' => 'new_job'
		))); ?>
    <br/>
	  <h2>Option 2: Use a URL</h2>
	  <form class="form-inline" action="/upload/url" method="post">
      <input type="text" class="input-xlarge" name="url" placeholder="URL to load">
      <button type="submit" class="btn btn-primary">Go!</button>
    </form>
		<ul class="muted">
		  <li>The URL should point to an acceptable filetype.</li>
		</ul>
	</div>
</div>