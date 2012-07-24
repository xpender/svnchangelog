svnchangelog
============

Tool to generate ChangeLogs using "svn log".

Also includes stuff made in branches or merged.

Searches for ^/tags/RELEASE_* and loops over all of them, collect commit messages and generates HTML output.

Not really nice code, but functionable.

Files need to be adjusted:

include/config.inc.php 
 -> Add projects like example

include/classes.inc.php
 -> Line 97 if you are using http:// OR svn+ssh:// or else
 -> Line 383 & 384: SVN Username & Password, in theory it should work without auth (read rights are sufficient) or kerberos ticket
 -> Line 226: Output folder, if not ./www

Folders "./tmp" and "./data" need to be created and writable.
