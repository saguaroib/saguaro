This directory, and its contents, contain a majority of the logic for Saguaro.

A lot of things are still in an early state and could be greatly improved:
 1. Regist
 2. Log
 3. MySQL backends
 
Why?
----
- Clear up clutter in *imgboard.php*. (previously 2000-3000, lines)
- Easier to find and fix bugs.
- Changes trickle up through easily scalable and reusable code.
  - The most obvious way to scale these files is in a multi-board setup.

Most changes to Saguaro will happen in these files. Rarely will *imgboard.php* need to be modified and if so not in excess.
