<?php $loggedIn = true; ?>

<?php if ($loggedIn): ?>
    <nav>
        <a href="/profile">My Profile</a>
        <a href="/logout">Logout</a>
    </nav>
<?php else: ?>
    <a href="/login">Login</a>
<?php endif; ?>
