<div class="row">
	<div class="span3">
		<ul class="nav nav-list" id="<?= $name ?>Tab">
			<? foreach ($forms as $row): ?>
				<? $title = $row['title'] ?>
				<? $form = $row['form'] ?>
				<? $id = $name.'_'.$form->name ?>
				<? $class = ($form->name === $active ? 'active' : '') ?>
				<li id="<?= $id ?>" class="<?= $class ?>">
					<a <? if(!$wizardMode): ?>href="#<?= $id ?>_content" data-toggle="tab"<?endif?>  style="padding: 15px"><?= $title ?></a>
				</li>
			<? endforeach ?>
		</ul>
	</div>
	<div class="span9 tab-content" id="<?= $name ?>TabContent">
		<? foreach($forms as $row): ?>
			<? $title = $row['title'] ?>
			<? $form = $row['form'] ?>
			<? $class = "tab-pane fade".($form->name === $active ? ' active in' : '') ?>
			<div id="<?= $name ?>_<?= $form->name ?>_content" class="<?= $class ?>">
				<?= $form->render() ?>
			</div>
		<? endforeach ?>
	</div>
</div>