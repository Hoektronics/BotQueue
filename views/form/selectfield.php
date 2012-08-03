<? if (!empty($options)): ?>
	<select <?=$id?> name="<?=$name?>" <?=$onchange?>>
		<? foreach ($options AS $key => $option): ?>
			<? if ($key == $value): ?>
				<option value="<?=$key?>" selected><?=$option?></option>
			<? else: ?>
				<option value="<?=$key?>"><?=$option?></option>				
			<? endif ?>
		<? endforeach ?>
	</select>
<? endif ?>
