<?php
echo "This is PHP code.";
?>

  <p>This is plain HTML.</p>

<?php
echo "Back to PHP.";
$name = 'John Doe';
$age = 30;
?>

<p>Welcome, <?= htmlspecialchars($name) ?>!</p>
<p>Your age: <?= $age ?></p>