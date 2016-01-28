To implement true fcgi support you need to perform next simple steps:

1. Write simple PHP file:

```
<?php

class YourClassName {
  public function __construct() {

    ... application initialization goes here  ...

  }
  public function run($request) {
    $data = $request->read();

    ... some action ...

    $request->setHeader('content-type', 'text/html');
    $reuqest->write($result);
  }
}
```

2. Write some nginx-like conf file /usr/loacel/etc/ultramilk.conf:

```
YourClassName {
  path: /path/to/your/php/file;
  port: 9999;
  host: 127.0.0.1;
  children: 5;
}
```

3. Execute imweasel/tools/imweasel.php

So now your application loaded, initialized and ready to receive fcgi requests. This approach has some limitation, you can't use php predefined variables like `$_FILE`, `$_POST` and so on and Beware of memory leaks!!!

P.S.: You have to install nginx, lighttpd or something else to work with imweasel server!