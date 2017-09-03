# golum-server
The server-side for Golum

There are some variables (i think in `components/initialization.php` and `components/common_requires.php`, mainly) that you will have to adapt to your server environment, along with the database connection. There may be other things that you will have to setup and adapt, but the ones described previously are the main ones.

In order to get realtime features such as live messaging, the composer_things/bin/push-server must be running in the conosle (`php path/to/composer_things/bin/push-server.php` is the command you need to run).

You will also have to import `database/golum.sql` (which is a MYSQL database). 
	
You could recreate the vendor directory by running `composer install` in the `composer_things/` directory, in that case, i highly advise you to comment the lines out that cause a `cannot handle token prior to` exception, these are related to the Google sign-in and can be found at `C:\wamp\www\Golum\composer_things\vendor\firebase\php-jwt\src\JWT.php`, around line 125. 
	
Just in case you end up editing `composer_things/composer.json`, don't edit the value of `firebase/php-jwt` in any way (again, related to Google sign-in, in versions not lower than 5, a `Signature invalid exception` would be thrown which appeared to be pretty hard to fix, and was possibly caused by a bug on their side).
	
Note that i don't use the built-in PHP sessions, instead, i use Symfony's "PDO session handler". I use this because it is necessary to do so if you want Ratchet to attach a session to each connection object with READ-ONLY access, so we are doing this merely because we want to get the session data for each connection in our websocket server. 
	
