<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span6">
  	  <h3>File Details</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Download URL:</th>
						<td><a href="<?=$file->getRealUrl()?>"><?=$file->getName()?></a></td>
					</tr>
					<tr>
						<th>Creator:</th>
						<td><?=$creator->getLink()?></td>
					</tr>
					<tr>
						<th>Type:</th>
						<td><?=$file->get('type')?></td>
					</tr>
					<tr>
						<th>Size:</th>
						<td><?= Utility::filesizeFormat($file->get('size'))?></td>
					</tr>
					<tr>
						<th>MD5 Hash:</th>
						<td><?= $file->get('hash')?></td>
					</tr>
					<tr>
						<th>Add Date:</th>
						<td><?=Utility::formatDateTime($file->get('add_date'))?>`</td>
					</tr>
					<tr>
						<th>Manage:</th>
						<td><a href="/job/create/file:<?=$file->id?>">re-run</a></td>
					</tr>
				</tbody>
			</table>
			<h3>File Jobs</h3>
			TODO: add file jobs here.
		</div>
		<div class="span6">
		  <iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400" src="<?=$file->getUrl()?>/render"></iframe>
		</div>
	</div>
<? endif ?>