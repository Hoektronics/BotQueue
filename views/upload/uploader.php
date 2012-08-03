<form action="https://<?=AMAZON_S3_BUCKET_NAME?>.s3.amazonaws.com/" method="post" enctype="multipart/form-data" onsubmit="return check_upload_form(this)">
	<input type="hidden" name="AWSAccessKeyId" value="<?=AMAZON_AWS_KEY?>"> 
	<input type="hidden" name="key" value="uploads/${filename}">
	<input type="hidden" name="acl" value="<?=$acl?>"> 
	<input type="hidden" name="success_action_redirect" value="<?=$redirect?>">
	<input type="hidden" name="policy" value="<?=$policy?>">
	<input type="hidden" name="signature" value="<?=$signature?>">
	<input type="hidden" name="Content-Type" value="">
	<input type="hidden" name="Content-Disposition" value="">

	<? if ($label): ?>
		<b><?= $label ?>: </b>&nbsp;
	<? endif ?>

	<input name="file" type="file"> <input type="submit" value="Upload File">
</form>
