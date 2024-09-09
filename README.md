Look in bin and the README there.
The folder `root` is where your document root is stored.
The apache daemon does not see the actual content files,
only a directory containing `index.php`, `.htaccess`, and `.php`.

Note: I am not any kind of expert software engineer,
and this code was largely written to satisfy my own needs,
and to learn my way around PHP. I just share it in case
anybody is interested.

Note that this is designed for a Lamp stack running on Linux,
and it makes use of symlinks.
