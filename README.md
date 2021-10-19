Bencodex reader/writer for PHP
==============================

[![Packagist Version][]][Packagist]
[![GitHub Actions Status][]][GitHub Actions]

[Packagist]: https://packagist.org/packages/bencodex/bencodex
[Packagist Version]: https://img.shields.io/packagist/v/bencodex/bencodex
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

- PHP 5.4 or later
- [iconv] extension (`--with-iconv`)

[iconv]: https://www.php.net/manual/en/book.iconv.php


Type correspondences
--------------------

| PHP                                    | Bencodex                  |
|----------------------------------------|---------------------------|
| Null                                   | Null                      |
| Boolean                                | Boolean                   |
| Integer                                | Integer                   |
| Double<sup>†</sup>                     | Integer (truncated)       |
| Numeric string<sup>‡</sup>             | Integer ≥ [`PHP_INT_MAX`] |
| String which can be decoded as Unicode | Text<sup>‡</sup>          |
| String otherwise                       | Binary<sup>‡</sup>        |
| List-like array<sup>※</sup>            | List                      |
| Map-like array<sup>†</sup>             | Dictionary                |
| Object                                 | Dictionary                |

*† One-way types only available for encoding.*

*‡ One-way types only available for decoding.*

*※ Determined by [`array_is_list()` function][array_is_list].*

[array_is_list]: https://www.php.net/manual/en/function.array-is-list
[`PHP_INT_MAX`]: https://www.php.net/manual/en/reserved.constants.php#constant.php-int-max

Usage
-----

- `\Bencodex\encode(mixed $value): string`: Encodes a PHP `$value` into Bencodex
  data.
- `\Bencodex\decode(string $bencodex): mixed`: Decodes Bencodex data into a PHP
  value.

The above APIs are merely façade, and optional parameters are omitted.
See the [complete API docs][1] as well.

[1]: https://dahlia.github.io/bencodex-php/


[Bencodex]: https://bencodex.org/
[Bencoding]: https://www.bittorrent.org/beps/bep_0003.html#bencoding
