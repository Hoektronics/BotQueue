<div class="row">
	<div class="span3">
		<ul class="nav nav-list" id="<?php echo $name ?>Tab">
			<?php foreach ($forms as $row): ?>
				<?php $title = $row['title'] ?>
				<?php $form = $row['form'] ?>
				<?php $id = $name.'_'.$form->name ?>
				<?php $class = ($form->name === $active ? 'active' : '') ?>
				<li id="<?php echo $id ?>" class="<?php echo $class ?>">
					<a <?php if(!$wizardMode): ?>href="#<?php echo $id ?>_content" data-toggle="tab"<?php endif ?>  style="padding: 15px"><?php echo $title ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="span9 tab-content" id="<?php echo $name ?>TabContent">
		<?php foreach($forms as $row): ?>
			<?php $title = $row['title'] ?>
			<?php $form = $row['form'] ?>
			<?php $class = "tab-pane fade".($form->name === $active ? ' active in' : '') ?>
			<div id="<?php echo $name ?>_<?php echo $form->name ?>_content" class="<?php echo $class ?>">
				<?php echo $form->render() ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>