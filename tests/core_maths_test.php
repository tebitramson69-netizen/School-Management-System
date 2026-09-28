<?php

/*
 * Unit tests for the pure core calculations (no database needed).
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Models/Score.php';
require_once __DIR__ . '/../src/Models/Result.php';
require_once __DIR__ . '/../src/Models/Term.php';

echo "Score::computeWeightedAverage\n";

$subjects = [
    ['subject_id' => 1, 'average_score' => 16.0],
    ['subject_id' => 2, 'average_score' => 10.0],
];

assert_same(13.0, Score::computeWeightedAverage($subjects, []), 'equal weights (16+10)/2');
assert_same(14.5, Score::computeWeightedAverage($subjects, [1 => 3, 2 => 1]), 'coefficients (48+10)/4');

$withNull = [
    ['subject_id' => 1, 'average_score' => 12.0],
    ['subject_id' => 2, 'average_score' => null],
];
assert_same(12.0, Score::computeWeightedAverage($withNull, [1 => 2, 2 => 5]), 'null average skipped');
assert_same(null, Score::computeWeightedAverage([], []), 'empty set => null');
assert_same(null, Score::computeWeightedAverage([['subject_id' => 1, 'average_score' => null]], []), 'all null => null');

echo "Term::globalSequence\n";

assert_same(1, Term::globalSequence('Term 1', 1), 'Term 1 seq 1 => 1');
assert_same(2, Term::globalSequence('Term 1', 2), 'Term 1 seq 2 => 2');
assert_same(3, Term::globalSequence('Term 2', 1), 'Term 2 seq 1 => 3');
assert_same(4, Term::globalSequence('Term 2', 2), 'Term 2 seq 2 => 4');
assert_same(5, Term::globalSequence('Term 3', 1), 'Term 3 seq 1 => 5');
assert_same(6, Term::globalSequence('Term 3', 2), 'Term 3 seq 2 => 6');
assert_same(1, Term::globalSequence('Annual', 1), 'name without number falls back to term 1');

echo "Result::assignPositions\n";

$ranked = Result::assignPositions([
    ['full_name' => 'Bimo',  'overall_average' => 15.0],
    ['full_name' => 'Ateba', 'overall_average' => 17.5],
    ['full_name' => 'Che',   'overall_average' => 15.0],
    ['full_name' => 'Dora',  'overall_average' => null],
]);

assert_same('Ateba', $ranked[0]['full_name'], 'highest average first');
assert_same(1, $ranked[0]['position'], 'top position is 1');
assert_same('Bimo', $ranked[1]['full_name'], 'tie broken alphabetically (Bimo before Che)');
assert_same(2, $ranked[1]['position'], 'tie shares position 2');
assert_same(2, $ranked[2]['position'], 'both tied students are 2nd');
assert_same('Dora', $ranked[3]['full_name'], 'no-result student ranked last');
assert_same(null, $ranked[3]['position'], 'no-result student has null position');
