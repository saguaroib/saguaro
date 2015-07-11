Execute somewhere in *imgboard.php*: (preferably an empty page, also change PATHTO)

```PHP
updatelog();
echo '<link rel="stylesheet" type="text/css" media="screen"  href="PATHTO/css/saguaba.css"/>';
include("_core/classes/thread.php");

$thread = new Thread();
$thread->format(1);
```

Replace 1 with the desired OP number. Replies will also work, but only as themselves.

In my testing I added a new case to the *switch()* at the bottom of *imgboard.php*:

```PHP
case 'test':
        updatelog();
        
        echo '<link rel="stylesheet" type="text/css" media="screen"  href="PATHTO/css/saguaba.css"/>';
        include("_core/classes/thread.php");

        $a = new Thread();
        $n = ($r) ? $r : 1;
        $a->format($n);
```

Then accessed `imgboard.php?mode=test&r=1`, where 1 is a valid OP number.
