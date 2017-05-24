<img src="/img/yao-ming.jpeg" style="float:right; margin-left: 20px;">

<p>Hah, just kidding!  Writing documentation at this stage takes time from writing code.  Still, here's some help for ya.</p>

<p>The API uses OAuth for authentication of client requests.  You'll want to tackle that first if you're not using the python client.</p>

<p>The API is still under development, and may be a moving target for a while.  It is functional, but your best shot at documentation is to look at the <a href="https://github.com/Hoektronics/BotQueue/blob/master/controllers/apiv1.php">API controller</a> for the server, as well as the <a href="https://github.com/Hoektronics/BotQueue/blob/master/bumblebee/botqueueapi.py">python client implementation</a>.</p>

<p>If you want to pitch in, or you spot a bug, please get ahold of me.  I'd love to get some extra eyes on the code, and I really hope to turn this into world-class software.</p>

<p>Here is a list of the currently implemented api function calls available:</p>

<ul>
  <li>requesttoken</li>
  <li>accesstoken</li>
  <li>listqueues</li>
  <li>queueinfo</li>
  <li>createqueue</li>
  <li>listjobs</li>
  <li>jobinfo</li>
  <li>grabjob</li>
  <li>findnewjob</li>
  <li>dropjob</li>
  <li>canceljob</li>
  <li>completejob</li>
  <li>createjob</li>
  <li>updatejobprogress</li>
  <li>listbots</li>
  <li>botinfo</li>
  <li>registerbot</li>
  <li>updatebot</li>
</ul>