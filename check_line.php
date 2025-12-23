<?php
$lines = file('index.php');
if (isset($lines[844])) {
    echo "Line 845 raw: " . $lines[844];
    echo "Line 845 encoded: " . json_encode($lines[844]);
} else {
    echo "Line 845 not found.";
}
?>
