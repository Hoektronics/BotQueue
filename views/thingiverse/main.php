<?php if (User::$me->get('thingiverse_token')): ?>
  Your Thingiverse Account has been connected with your BotQueue account.
  <br/><br/>
  <?php echo User::$me->get('thingiverse_token') ?>
  <br/><br/>
  <pre>
    <?php echo print_r($my_info) ?>
  </pre>
<?php else: ?>
  Want to get started using BotQueue with Thingiverse?  <a class="btn btn-primary" href="https://www.thingiverse.com/login/oauth/authorize?client_id=<?php echo THINGIVERSE_API_CLIENT_ID ?>">Authorize App</a>
<?php endif ?>