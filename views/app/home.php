<div class="row">
	<div class="span6">
		<h2>Bumblebee</h2>
		<p>
			Bumblebee is a cross-platform python app designed to interface with BotQueue.  This application will pull down jobs from BotQueue, and drive your 3D printer to produce them.  It is currently capable of controlling the following machines:
			<ul>
				<li>MakerBot Thing-o-Matic</li>
				<li>MakerBot Replicator</li>
				<li>Any machine using <a href="https://github.com/grbl/grbl/">grbl</a>, <a href="https://github.com/kliment/Sprinter">Sprinter</a>, <a href="https://github.com/ErikZalm/Marlin/">marlin</a>, or other direct gcode parsing firmware</li>
			</ul>
		</p>
		<h3>Getting Bumblebee</h3>
		<p>
			Bumblebee is currently under development, and a release will be made soon.  In the meantime, you can get it from the <a href="git@github.com:Hoektronics/BotQueue.git">BotQueue github repository</a>.
		</p>
		<h3>Installing Bumblebee</h3>
		<p>
			TBD.
		</p>
	</div>
	<div class="span6">
		<?=Controller::byName('apiv1')->renderView('home')?>
	</div>
</div>