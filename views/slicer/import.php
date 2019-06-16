<? if ($megaerror): ?>
  <?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? endif ?>