Upload Many
=============

Makes multiple uploads possible in symphony.

WARNING
===========

This is a pre-pre alpha release.

This has not been tested, and is not yet complete.

Installation
-------------

This is a tough one. Normally, this would say something like: unzip in the extensions folder, and activate.

Since this is a pre-alpha release, this extension still needs some modifications to the core.
Don't worry though, it's just one small change.

In index.php (in the root folder), just after the `<?php` tags, add the following line:

	if(isset($_POST['PHPSESSID'])) $_COOKIE['PHPSESSID'] = $_POST['PHPSESSID'];
	
Then, installing should be like you are used to.
	
