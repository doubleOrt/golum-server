# golum-server
The server-side for Golum

There are some variables (i think in `initialization.php`, mainly) that you will have to adapt to your server environment, along with the database connection.

In order to get realtime features such as live messaging, the composer_things/bin/push-server must be running in the conosle (`php path/to/composer_things/bin/push-server.php` is the command you need to run).

There may be other things that you will have to setup and adapt.
