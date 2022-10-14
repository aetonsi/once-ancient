<?php

namespace Aetonsi\OnceAncient;

require __DIR__ . '/../vendor/autoload.php';

class Tester
{
    private $prefix;

    static function rnds($prefix = '')
    {
        $prefix = __FUNCTION__ . '_static_' . strtoupper($prefix);
        return once(function () use ($prefix) {
            return "\n$prefix === " . rand(1, 1000);
        });
    }

    function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    function __invoke($prefix = '')
    {
        return $this->rnd("__invoke_$prefix");
    }

    function rnd($prefix = '')
    {
        $prefix = __FUNCTION__ . '_' . $this->prefix . '_' . strtoupper($prefix);
        return once(function () use ($prefix) {
            return "\n$prefix === " . rand(1, 1000);
        });
    }

    function callrnd($prefix = '')
    {
        $that = $this;
        return once(function () use ($prefix, $that) {
            return $that->rnd($prefix);
        });
    }
}

function globalrnd($prefix = '')
{
    $prefix = __FUNCTION__ . '_' . strtoupper($prefix);
    return once(function () use ($prefix) {
        return "\n$prefix === " . rand(1, 1000);
    });
}

/////////////////////////////

define("TEST_ARG", 'testxxx_');
$obj1 = new tester('obj1');
$obj2 = new tester('obj2');
$callables = [
    '\Aetonsi\OnceAncient\globalrnd',
    function () {
        return globalrnd('closure');
    },
    'Aetonsi\OnceAncient\Tester::rnds',
    function () {
        return Tester::rnds('closureS');
    },
    $obj1,
    [$obj1, 'rnd'],
    [$obj1, 'callrnd'],
    $obj2,
    [$obj2, 'rnd'],
    [$obj2, 'callrnd']
];

///////////////////////

$firstResults = [];
foreach ($callables as $fn) {
    $firstResults[] = call_user_func($fn, TEST_ARG);
}

foreach ($callables as $resultIndex => $fn) {
    for ($i = 0; $i < 1000; $i++) {
        $result = call_user_func($fn, TEST_ARG);
        if ($result !== $firstResults[$resultIndex]) {
            var_dump(" !!!!!!!!!!!!!!!!! RESULTS DO NOT MATCH: ", $result, $firstResults[$resultIndex]);
            exit;
        } else {
            echo "$resultIndex x $i ok...";
        }
    }
}

echo "\n\n\n\n\n\nresults tested 1k times: \n\n";
print_r($firstResults);

echo "\n\n\n !!!!!!!!!!!!! EVERYTHING MATCHES";
