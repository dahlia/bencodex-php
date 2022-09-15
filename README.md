Bencodex reader/writer for PHP
==============================

[![Packagist Version][]][Packagist]
[![MIT License][]](LICENSE)
[![GitHub Actions Status][]][GitHub Actions]

[Packagist]: https://packagist.org/packages/bencodex/bencodex
[Packagist Version]: http://poser.pugx.org/bencodex/bencodex/v
[MIT License]: http://poser.pugx.org/bencodex/bencodex/license
[GitHub Actions Status]: https://github.com/dahlia/bencodex-php/actions/workflows/build.yaml/badge.svg
[GitHub Actions]: https://github.com/dahlia/bencodex-php/actions/workflows/build.yaml

This package implements [Bencodex] serialization format which extends
[Bencoding].  Complianet with Bencodex 1.2.

~~~ php
php > echo Bencodex\encode(['foo' => 123, 'bar' => [true, false]]);
du3:barltfeu3:fooi123ee
php > var_dump(Bencodex\decode('du3:barltfeu3:fooi123ee'));
object(stdClass)#4 (2) {
  ["bar"]=>
  array(2) {
    [0]=>
    bool(true)
    [1]=>
    bool(false)
  }
  ["foo"]=>
  int(123)
}
~~~


Requirements
------------

[![PHP Version Requirement][]][Packagist]

- PHP 5.4 or later
- [iconv] extension (`--with-iconv`)

[PHP Version Requirement]: http://poser.pugx.org/bencodex/bencodex/require/php
[iconv]: https://www.php.net/manual/en/book.iconv.php


Type correspondences
--------------------

| PHP                                    | Bencodex                  |
|----------------------------------------|---------------------------|
| Null                                   | Null                      |
| Boolean                                | Boolean                   |
| Integer                                | Integer                   |
| Double[^1]                             | Integer (truncated)       |
| Numeric string[^2]                     | Integer ≥ [`PHP_INT_MAX`] |
| String which can be decoded as Unicode | Text[^2]                  |
| String otherwise                       | Binary[^2]                |
| List-like array[^3]                    | List                      |
| Map-like array[^1]                     | Dictionary                |
| Object                                 | Dictionary                |

[^1]: One-way types only available for encoding.
[^2]: One-way types only available for decoding.
[^3]: Determined by [`array_is_list()` function][array_is_list].

[array_is_list]: https://www.php.net/manual/en/function.array-is-list
[`PHP_INT_MAX`]: https://www.php.net/manual/en/reserved.constants.php#constant.php-int-max

Usage
-----

- [`\Bencodex\encode(mixed $value): string`][encode]: Encodes a PHP `$value`
  into Bencodex data.
- [`\Bencodex\decode(string $bencodex): mixed`][decode]: Decodes Bencodex data
  into a PHP value.

The above APIs are merely façade, and optional parameters are omitted.
See the [complete API docs][1] as well.

[encode]: https://dahlia.github.io/bencodex-php/namespaces/bencodex.html#function_encode
[decode]: https://dahlia.github.io/bencodex-php/namespaces/bencodex.html#function_decode
[1]: https://dahlia.github.io/bencodex-php/


[Bencodex]: https://bencodex.org/
[Bencoding]: https://www.bittorrent.org/beps/bep_0003.html#bencoding
