<?php

$line = '$CATEGORY: test value';
echo "Line: $line\n";

$result = preg_match('/^\$CATEGORY:\s*(.*)/', $line, $match);
echo 'Result: '.($result ? 'YES' : 'NO')."\n";
if ($match) {
    echo 'Match: '.$match[1]."\n";
}
