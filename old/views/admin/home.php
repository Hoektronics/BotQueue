<script>
	function setActiveForm() {
		$('#editTab').children('li').each(function() {
			var tab_id = $(this).attr('id');
			if(tab_id == 'admin_<?=$active_form?>') {
				$(this).attr("class", "active");
			}
			<? if(!$setup_mode): ?>
			$(this).children('a').each(function() {
				$(this).attr('href', '#'.concat(tab_id).concat('_content'));
				$(this).attr('data-toggle', 'tab');
			});
			<? endif ?>
		});
		var content = $('#editTabContent');
		content.children('div').each(function() {
			var content_id = $(this).attr('id');
			if(content_id == 'admin_<?=$active_form?>_content') {
				$(this).attr('class', 'tab-pane fade active in');
			} else {
				$(this).attr('class', 'tab-pane fade');
			}
		});
		content.attr('style', '');
	}

	window.onload = setActiveForm;
</script>
<div class="row">
	<div class="span3">
		<ul class="nav nav-list" id="editTab">
			<li id="admin_status">
				<a>Status</a>
			</li>
			<li id="admin_database">
				<a>Database Connection</a>
			</li>
			<li id="admin_storage">
				<a>File Storage</a>
			</li>
			<li id="admin_email">
				<a>Email Settings</a>
			</li>
			<li id="admin_thingiverse">
				<a>Thingiverse Settings</a>
			</li>
		</ul>
	</div>
	<div class="span9 tab-content" id="editTabContent" style="display:none">
		<div id="admin_status_content">
			At least this is accessible.
		</div>
		<div id="admin_database_content">
			Database connection is unknown!
		</div>
		<div id="admin_storage_content">
			This will not be fun to implement.
		</div>
		<div id="admin_email_content">
			I like getting emails too.
		</div>
		<div id="admin_thingiverse_content">
			Does this thing connect to thingiverse?
		</div>
	</div>
</div>