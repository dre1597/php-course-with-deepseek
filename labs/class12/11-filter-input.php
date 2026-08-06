<?php
// URL: http://localhost/page.php?id=42&email=test@example.com&color=%23ff0000

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    die('Invalid ID.');
}

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);

$color = filter_input(INPUT_GET, 'color', FILTER_SANITIZE_STRING);

// filter_input_array — validate multiple fields at once
$filters = [
    'name'  => FILTER_SANITIZE_STRING,
    'email' => FILTER_VALIDATE_EMAIL,
    'age' => [
        'filter'  => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0, 'max_range' => 150],
    ],
];

$data = filter_input_array(INPUT_POST, $filters);

foreach ($data as $field => $value) {
    if ($value === false || $value === null) {
        echo "Field '{$field}' is invalid.<br>\n";
    }
}
