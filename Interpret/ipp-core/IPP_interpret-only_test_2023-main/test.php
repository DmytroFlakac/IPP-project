#!/usr/bin/env php
<?php

$passed = 0;
$failed = 0;
$interpret = "..\interpret.php"; // Adjust the path as needed

if (getenv("INTERPRET")) {
    $interpret = getenv("INTERPRET");
}

$directoryWithTests = getcwd(); // Assuming you're in the directory with tests
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryWithTests));

foreach ($files as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'src') continue;

    $src = $file->getPathname();
    $input = str_replace('.src', '.in', $src);
    $myOut = str_replace('.src', '.my_out', $src);
    $myRc = str_replace('.src', '.my_rc', $src);
    $expectedOut = str_replace('.src', '.out', $src);
    $expectedRc = str_replace('.src', '.rc', $src);

    // Execution (removed timeout for compatibility)
    $command = "php \"$interpret\" --source=\"$src\" --input=\"$input\" > \"$myOut\"";
    exec($command, $output, $returnVar);
    file_put_contents($myRc, $returnVar);

    $pass = true;
    // Check stdout
    if (trim(file_get_contents($myOut)) !== trim(file_get_contents($expectedOut))) {
        echo "\033[31mBad stdout $src\n";
        $pass = false;
    }

    // Check return code
    if (trim(file_get_contents($myRc)) !== trim(file_get_contents($expectedRc))) {
        echo "\033[31mBad RC $src\n";
        $pass = false;
    }

    if ($pass) {
        echo "\033[32mPASS $src\n";
        $passed++;
    } else {
        $failed++;
    }
}

echo "PASSED: $passed\n";
echo "FAILED: $failed\n";
