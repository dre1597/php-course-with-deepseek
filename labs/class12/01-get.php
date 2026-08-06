<?php
// URL: http://localhost/page.php?name=John&age=28&city=São+Paulo

echo "Name: " . ($_GET['name'] ?? 'Not provided') . "<br>\n";
echo "Age: " . ($_GET['age'] ?? 'Not provided') . "<br>\n";
echo "City: " . ($_GET['city'] ?? 'Not provided') . "<br>\n";

// Iterate over all GET parameters
foreach ($_GET as $key => $value) {
    echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>\n";
}
