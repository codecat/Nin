<p>Add a message:</p>

<form method="post" action="/index/addmessage">
	<p>Nickname:<br><input type="text" name="nick" /></p>
	<p>Message:<br><input type="text" name="msg" /></p>
	<input type="submit" value="Send" />
</form>

<p>Messages:</p>

<ul>
	<?php foreach($data as $msg) { ?>
		<li><?= $msg->Name ?>: <?= $msg->Message ?> <a href="index/message?id=<?= $msg->ID ?>">(permalink)</a> <a href="index/deletemessage?id=<?= $msg->ID ?>">(delete)</a></li>
	<?php } ?>
</ul>
