<?= Controller::byName('user')->renderView('loginbox'); ?>
<br/>
<a href="/">Home</a> |
<a href="/queue/create">Create Queue</a> |
<a href="/bot/register">Register Bot</a> |
<a href="/upload">Upload a Job</a> |
<a href="/api/v1">API</a>

<br/>
<?= $content ?>