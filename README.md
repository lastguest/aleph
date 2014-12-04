<h1><img src="docs/aleph.png" height="100"> Aleph</h1>

[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/lastguest/aleph?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/lastguest/aleph.svg)](https://travis-ci.org/lastguest/aleph)
[![Total Downloads](https://poser.pugx.org/lastguest/aleph/downloads.svg)](https://packagist.org/packages/lastguest/aleph)
[![Latest Stable Version](https://poser.pugx.org/lastguest/aleph/v/stable.svg)](https://packagist.org/packages/lastguest/aleph)
[![Latest Unstable Version](https://poser.pugx.org/lastguest/aleph/v/unstable.svg)](https://packagist.org/packages/lastguest/aleph)
[![License](https://poser.pugx.org/lastguest/aleph/license.svg)](https://packagist.org/packages/lastguest/aleph)
[![HHVM Status](http://hhvm.h4cc.de/badge/lastguest/aleph.svg)](http://hhvm.h4cc.de/package/lastguest/aleph)

> Aleph is a very simple PHP framework for very small sites.

## Installation

Install via [composer](https://getcomposer.org/download/):

```bash
$ composer require lastguest/aleph -o
```

Download the framework file:

```bash
dist/aleph.min.php
```

Remote include the framework file: (needs `allow_url_include = true` in php.ini)

```php
<?php
include "https://raw.githubusercontent.com/lastguest/aleph/master/dist/aleph.min.php";
```


## Documentation

See the [wiki](https://github.com/lastguest/aleph/wiki).


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

