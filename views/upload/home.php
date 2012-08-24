<div class="row">
	<div class="span6">
		<?= Controller::byName('upload')->renderView('uploader', array(
			'payload' => array(
			'type' => 'new_job'
		))); ?>
	</div>
	<div class="span6">
		<h2>How it works</h2>
		<ol>
			<li>You upload a GCode file to our server.</li>
			<li>You specify the queue and # of prints you want.</li>
			<li>The client software downloads and executes each job.</li>
			<li>You take your shiny new stuff and do awesomeness.</li>
		</ol>
		
		<h2>Accepted filetypes</h2>
		<ul>
			<li>At this point in time, we only accept <strong>.gcode</strong> files.</li>
			<li>In <i>Version 2</i>, we will be adding online slicer support.  Stay tuned.</li>
		</ul>
	</div>
</div>