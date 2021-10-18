Bencodex reader/writer for PHP
==============================

[![GitHub Actions Status][]][GitHub Actions]

[GitHub Actions Status]: https://github.com/dahlia/bencodex-php/actions/workflows/test.yaml/badge.svg
[GitHub Actions]: https://github.com/dahlia/bencodex-php/actions/workflows/test.yaml

This package implements [Bencodex] serialization format which extends
[Bencoding].


Usage
-----

- `\Bencodex\encode(mixed $value): string`: Encodes a PHP `$value` into Bencodex
  data.

The above APIs are merely façade, and optional parameters are omitted.
See the [complete API docs][1] as well.

[1]: https://dahlia.github.io/bencodex-php/


[Bencodex]: https://bencodex.org/
[Bencoding]: https://www.bittorrent.org/beps/bep_0003.html#bencoding
