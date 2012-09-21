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
		</ul>
	</div>
	<div class="span6">
	  <h2>Choose the file to print:</h2>
		<?= Controller::byName('upload')->renderView('uploader', array(
			'payload' => array(
			'type' => 'new_job'
		))); ?>
	</div>
</div>