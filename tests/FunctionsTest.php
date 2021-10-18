<?php

namespace Bencodex\Tests;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testEncode()
    {
        $value = ['utf-8' => '단팥', 'euc-kr' => "\xb4\xdc\xc6\xcf"];
        $this->assertEquals(
            "du6:euc-kr4:\xb4\xdc\xc6\xcfu5:utf-8u6:단팥e",
            \Bencodex\encode($value)
        );
        $this->assertEquals(
            "du6:euc-kru6:단팥u5:utf-86:단팥e",
            \Bencodex\encode($value, 'euc-kr')
        );
        $this->assertEquals(
            "d6:euc-kr4:\xb4\xdc\xc6\xcf5:utf-86:단팥e",
            \Bencodex\encode($value, null, null)
        );
    }

    const TEXT_ENCODING = 'utf-32le';
    const UTF32LE_BOM = "\xff\xfe\x00\x00";

    /**
     * @dataProvider specProvider
     */
    public function testEncodeOnSpec($specName, $tree, $bencodexData)
    {
        $specPath = __DIR__ . '/spec/testsuite';
        $this->assertTrue(
            is_dir($specPath),
            "No specification test suite: $specPath; " .
            "please initialize the Git submodules:\n" .
            "\tgit submodule update --init --recursive"
        );
        $encoded = \Bencodex\encode(
            $tree,
            self::TEXT_ENCODING,
            self::TEXT_ENCODING,
            true
        );
        $this->assertEquals(
            $bencodexData,
            $encoded,
            "Not compliant with the spec: $specName"
        );
    }

    public function specProvider()
    {
        $specPath = __DIR__ . '/spec/testsuite';
        if (!is_dir($specPath)) {
            return [['error.json', false, 'f']];
        }

        $d = opendir($specPath);
        $spec = [];
        while (($f = readdir($d)) !== false) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'json') {
                continue;
            }
            $jsonPath = "$specPath/$f";
            $dataPath = substr($jsonPath, 0, -4) . 'dat';
            if (!file_exists($dataPath)) {
                continue;
            }
            $json = json_decode(file_get_contents($jsonPath), true);
            $data = file_get_contents($dataPath);
            array_push($spec, [$f, self::parseTree($json), $data]);
        }
        closedir($d);
        return $spec;
    }

    public static function parseTree($json)
    {
        switch ($json['type']) {
            case 'null':
                return null;
            case 'boolean':
                return $json['value'];
            case 'integer':
                return intval($json['decimal']);
            case 'text':
                return self::UTF32LE_BOM .
                    iconv('utf-8', self::TEXT_ENCODING, $json['value']);
            case 'binary':
                return base64_decode($json['base64']);
            case 'list':
                return array_map(__METHOD__, $json['values']);
            case 'dictionary':
                $dict = new \stdClass();
                foreach ($json['pairs'] as $pair) {
                    $key = self::parseTree($pair['key']);
                    $value = self::parseTree($pair['value']);
                    $dict->$key = $value;
                }
                return $dict;
        }
        throw new \TypeError("Unsupported type: ${json['type']}.");
    }
}
