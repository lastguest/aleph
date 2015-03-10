<h1><img src="docs/aleph.png" height="100"> Aleph</h1>

[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/lastguest/aleph?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Total Downloads](https://poser.pugx.org/lastguest/aleph/downloads.svg)](https://packagist.org/packages/lastguest/aleph)
[![Latest Stable Version](https://poser.pugx.org/lastguest/aleph/v/stable.svg)](https://packagist.org/packages/lastguest/aleph)
[![Latest Unstable Version](https://poser.pugx.org/lastguest/aleph/v/unstable.svg)](https://packagist.org/packages/lastguest/aleph)
[![License](https://poser.pugx.org/lastguest/aleph/license.svg)](https://packagist.org/packages/lastguest/aleph)

> Aleph is a very simple PHP framework for very small sites.

## Installation

Install via [composer](https://getcomposer.org/download/):

```bash
$ composer require lastguest/aleph -o
```

Or download only [the framework file](https://raw.githubusercontent.com/lastguest/aleph/master/src/aleph.php)

Or remote include the framework file: (needs `allow_url_include = true` in php.ini)

```php
<?php
include "https://raw.githubusercontent.com/lastguest/aleph/master/src/aleph.php";
```


## Documentation

### Boostrap
---

Include composer `vendor/autoload.php` 

```php
<?php
include 'vendor/autoload.php';
```

or directly the `aleph.php` file in your front controller:

```php
<?php
include 'aleph.php';
```


### URL Routing
---


```php

// The index route
get('/',function(){
  echo "<h1>Hello!</h1>";
});

// Listen POST on /
post('/',function(){
  echo "<h1>Received POST Data:</h1><pre>";
  print_r($_POST);
  echo "</pre>";
});
```

If you return an array or an object it will served as JSON 

```php
get('/api/todos',function(){
  return [
    [ "id"=>1, "text"=>"Write documentation" ],
    [ "id"=>2, "text"=>"Smile" ],
    [ "id"=>3, "text"=>"Play more games" ],
    [ "id"=>4, "text"=>"Conquer the World" ],
  ];
});
```

The response will be :

```json
[
    {
        "id": 1,
        "text": "Write documentation"
    },
    {
        "id": 2,
        "text": "Smile"
    },
    {
        "id": 3,
        "text": "Play more games"
    },
    {
        "id": 4,
        "text": "Conquer the World"
    }
]
```

### Database
---


#### Init database DSN

```php
database('init','mysql:host=localhost;dbname=test','root','root');
```

#### Run query and get single column

```php
$uid = sql_value('select id from users where username = ? limit 1', array($username));
```

#### Run query and get single row

```php
$user = sql_row('select * from users where username = ?', array($username));
echo $user->email;
```

#### Run query and iterate all returned rows

```php
sql_each('select * from users', function($user){
  echo "<li><a href="mailto:{$user->email}">{$user->name}</a></li>";
});
```

Passing parameters:

```php
sql_each('select * from users where activated = ?', function($user){
  echo "<li><a href="mailto:{$user->email}">{$user->name}</a></li>";
}, array('YES'));
```

#### Exec sql command

```php
if ( sql('delete from users where id = ?',array(123)) ) echo "User deleted.";
```


### Service
---

The Service function is a small DI container.

#### Register a factory method

```php
class TestService {
	public $value;
	function __construct($x){ $this->value = $x; }
}

service('test',function($x){
	return new TestService($x);
});
```

#### Make service instances

```php
$a = service('test',3);
$b = service('test',5);
```

```json
[{"value":3},{"value":5}]
```

#### Register a singleton service

```php
service('test',function($x){
	static $instance = null;
	return $instance === null ? $instance = new TestService($x) : $instance;
});
```

Now if we call multiple times the `service('test')` function we got the singleton instance every time :

```php
$a = service('test',3);
$b = service('test',5);
$c = service('test');
```

```json
[{"value":3},{"value":3},{"value":3}]
```

======================

## Contributing

How to get involved:

1. [Star](https://github.com/lastguest/aleph/stargazers) the project!
2. Answer questions that come through [GitHub issues](https://github.com/lastguest/aleph/issues?state=open)
3. [Report a bug](https://github.com/lastguest/aleph/issues/new) that you find


Core follows the [GitFlow branching model](http://nvie.com/posts/a-successful-git-branching-model). The ```master``` branch always reflects a production-ready state while the latest development is taking place in the ```develop``` branch.

Each time you want to work on a fix or a new feature, create a new branch based on the ```develop``` branch: ```git checkout -b BRANCH_NAME develop```. Only pull requests to the ```develop``` branch will be merged.

Pull requests are **highly appreciated**.

Solve a problem. Features are great, but even better is cleaning-up and fixing issues in the code that you discover.

## Versioning

Core is maintained by using the [Semantic Versioning Specification (SemVer)](http://semver.org).


## Copyright and license

Copyright 2014 [Stefano Azzolini](http://dreamnoctis.com) under the [MIT license](LICENSE.md).



[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/lastguest/aleph/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

