<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<?= $wizard->render() ?>
	<script>
		function update_driver_form() {
			$(':submit').attr("disabled", "disabled");
			var token_id = $('#oauth_token_dropdown').find(":selected").val();
			var driver = $('#driver_name_dropdown').find(":selected").val();

			var edit_area = $('#driver_edit_area');
			edit_area.html("<span class='muted'>Loading...</span>");
			edit_area.load('/bot:<?=$bot_id?>/driverform:' + driver + '/token:' + token_id,
				function () {
					$(':submit').removeAttr("disabled");
				});
		}

		$(update_driver_form);

		function update_queues(element) {
			var last = $("select[id|='queue']:last")[0];
			var parent = $(element).parent().parent();
			if(last.id != element.id && element.value == "0") {
				parent.remove();
			} else if(last.id == element.id && element.value != "0") {
				parent = $(last).parent().parent();
				var newElement = parent.clone();
				var newSelect = newElement.find("select")[0];
				var newID = last.id.substr(6);
				newSelect.id = newSelect.name = "queue-" + (++newID);
				parent.after(newElement);
			}
		}

		function update_slice_config_dropdown(element) {
			var engine_id = $("select#slice_engine_dropdown option:selected").val();
			var dropDown = $("select#slice_config_dropdown");
			var submit = $(":submit");
			dropDown.attr("disabled", "disabled");
			submit.attr("disabled", "disabled");
			dropDown.load("/ajax/bot/slice_config_select", {"id": engine_id}, function () {
				dropDown.removeAttr("disabled");
				submit.removeAttr("disabled");
			});
		}
	</script>
<? endif ?>