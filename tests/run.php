<?php

/*
 * Minimal test runner (no Composer / PHPUnit).
 *
 * Run:  php tests/run.php
 *
 * Covers the pure, DB-free core calculations. Exit code is
 * non-zero if any assertion fails, so it can gate CI later.
 */

declare(strict_types=1);

$GLOBALS['__tests'] = ['pass' => 0, 'fail' => 0];

function assert_true(bool $cond, string $msg): void
{
    if ($cond) {
        $GLOBALS['__tests']['pass']++;
    } else {
        $GLOBALS['__tests']['fail']++;
        echo "  FAIL: {$msg}\n";
    }
}

function assert_same($expected, $actual, string $msg): void
{
    $ok = $expected === $actual;
    if (!$ok) {
        $msg .= ' (expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true) . ')';
    }
    assert_true($ok, $msg);
}

require __DIR__ . '/core_maths_test.php';

$t = $GLOBALS['__tests'];
echo "\n" . ($t['fail'] === 0 ? 'PASS' : 'FAIL')
    . ": {$t['pass']} passed, {$t['fail']} failed\n";

exit($t['fail'] === 0 ? 0 : 1);
