Execute somewhere in *imgboard.php*: (preferabbly an empty page)

```PHP
updatelog();
echo '<link rel="stylesheet" type="text/css" media="screen"  href="http://192.168.1.75/saguaro2/css/saguaba.css" title="Sagurichan"/>';
include("_core/classes/thread.php");

$thread = new Thread();
$thread->format(1);
```

Replace 1 with the desired OP number. Replies will work, but only as themselves.