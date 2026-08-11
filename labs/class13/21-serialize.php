<?php

class Counter
{
    private array $log;

    public function __construct()
    {
        $this->log = [];
    }

    public function increment(): void
    {
        $this->log[] = date('H:i:s');
    }

    public function count(): int
    {
        return count($this->log);
    }

    public function __serialize(): array
    {
        return ['log' => $this->log];
    }

    public function __unserialize(array $data): void
    {
        $this->log = $data['log'];
    }
}

session_start();

if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = new Counter();
}

$counter = $_SESSION['counter'];
$counter->increment();
$_SESSION['counter'] = $counter;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Serialize — Contador com Sessão</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 40px auto; text-align: center; }
        .count { font-size: 3em; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Contador de visitas</h1>
    <p class="count"><?= $counter->count() ?></p>
    <p>Recarregue a página para incrementar.</p>
    <p><a href="?reset=1">Resetar</a></p>

<?php if (isset($_GET['reset'])): ?>
    <?php unset($_SESSION['counter']); ?>
    <p>Contador resetado.</p>
<?php endif; ?>
</body>
</html>
