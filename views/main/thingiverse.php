<? if (User::$me->get('thingiverse_token')): ?>
  Your Thingiverse Account has been connected with your BotQueue account.
  <br/><br/>
  <?= User::$me->get('thingiverse_token')?>
  <br/><br/>
  <pre>
    <?=print_r($my_info)?>
  </pre>
  <pre>
    <?=print_r($thing)?>
  </pre>
  <pre>
    <?=print_r($files)?>
  </pre>
<? else: ?>
  Want to get started using BotQueue with Thingiverse?  <a class="btn btn-primary" href="https://www.thingiverse.com/login/oauth/authorize?client_id=<?=THINGIVERSE_API_CLIENT_ID?>">Authorize App</a>
<? endif ?>