<?php

ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

require_once 'MessageSender.php';
require_once 'Database.php';
require_once 'Router.php';
require_once 'Announcer.php';
require_once 'config.php';

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

function netMatch($CIDR,$IP)
{
    list ($net, $mask) = explode ('/', $CIDR);
    return ( ip2long ($IP) & ~((1 << (32 - $mask)) - 1) ) == ip2long ($net);
}

function isFromTelegram()
{
    return (netMatch("149.154.160.0/20", $_SERVER['REMOTE_ADDR']) ||
            netMatch("91.108.4.0/22", $_SERVER['REMOTE_ADDR']));
}

$ms = new MessageSender($token, $proxy);
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD,
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']);

if (isFromTelegram())
{
    $response = file_get_contents('php://input');
    $update = json_decode($response, true);
    $router = new Router($ms, $pdo, $botname);
    $router->route($update);
}
else
{
    if (isset($_POST['token']) && $_POST['token'] == $token)
    {
        $announcer = new Announcer($ms, $pdo);
        $announcer->announce($_POST['title'], $_POST['text'], $_POST['url']);
    }
}
?>

<form method="POST" action="/bot/al/hook.php">
	Title <br>
	<input type="text" value="Вышла новая серия!" name="title"> <br>
	Text <br>
	<input type="text" value="<?php echo $_POST['text'] ?? ''; ?>" name="text"> <br>
	URL <br>
	<input type="text" value="<?php echo $_POST['url'] ?? ''; ?>" name="url"> <br>
	Token <br>
	<input type="text" value="" name="token"> <br>
	<input type="submit" value="send" name="submit"> <br>
</form>
