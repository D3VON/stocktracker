
// connect to the mysql database server.
if (!($link = mysql_connect (DB_HOST, DB_USERNAME, DB_USERPASS))) die(mysql_error());
echo "success in database connection....<br>";

// select the ecard database we want to access.
if (!mysql_select_db(ECARD_DATABASE)) die(mysql_error());
echo "success in database selection....<br>";