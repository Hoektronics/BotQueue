<div class="row">
	<div class="span3">
		<ul class="nav nav-list" id="<?php echo $name ?>Tab">
			<? foreach ($forms as $row): ?>
				<? $title = $row['title'] ?>
				<? $form = $row['form'] ?>
				<? $id = $name.'_'.$form->name ?>
				<? $class = ($form->name === $active ? 'active' : '') ?>
				<li id="<?php echo $id ?>" class="<?php echo $class ?>">
					<a <? if(!$wizardMode): ?>href="#<?php echo $id ?>_content" data-toggle="tab"<?endif ?>  style="padding: 15px"><?php echo $title ?></a>
				</li>
			<? endforeach ?>
		</ul>
	</div>
	<div class="span9 tab-content" id="<?php echo $name ?>TabContent">
		<? foreach($forms as $row): ?>
			<? $title = $row['title'] ?>
			<? $form = $row['form'] ?>
			<? $class = "tab-pane fade".($form->name === $active ? ' active in' : '') ?>
			<div id="<?php echo $name ?>_<?php echo $form->name ?>_content" class="<?php echo $class ?>">
				<?php echo $form->render() ?>
			</div>
		<? endforeach ?>
	</div>
</div>