# Projeto 02: Lista de Tasks (To-Do List)

## Objetivo

Criar uma aplicação de To-Do List em PHP puro usando sessão do servidor para persistência. Sem banco de dados. O usuário pode adicionar, concluir, desmarcar e remover tarefas, com feedback visual imediato.

## Estrutura de Arquivos

```
projetos/todolist/
    index.php
```

---

## Código Completo

```php
<?php
// ==========================================
// index.php — To-Do List com PHP + Sessão
// ==========================================
session_start();

// Inicializa lista de tasks
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Inicializa IDs automáticos
if (!isset($_SESSION['proximo_id'])) {
    $_SESSION['proximo_id'] = 1;
}

// Inicializa mensagem flash
$message = '';
$messageType = '';

// ========== AÇÕES ==========
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'geral');

    if ($description === '') {
        $message = 'A descrição da tarefa é obrigatória.';
        $messageType = 'erro';
    } elseif (strlen($description) > 200) {
        $message = 'A tarefa deve ter no máximo 200 caracteres.';
        $messageType = 'erro';
    } elseif (!in_array($category, ['geral', 'trabalho', 'pessoal', 'estudo'])) {
        $message = 'Category inválida.';
        $messageType = 'erro';
    } else {
        $_SESSION['tasks'][] = [
            'id'            => $_SESSION['proximo_id']++,
            'description'     => $description,
            'category'     => $category,
            'completed'     => false,
            'created_at'     => date('Y-m-d H:i:s'),
        ];
        $message = 'Task adicionada com sucesso!';
        $messageType = 'success';
    }
    // Redireciona para evitar reenvio
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $messageType;
    header('Location: index.php');
    exit;
}

if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    foreach ($_SESSION['tasks'] as &$task) {
        if ($task['id'] === $id) {
            $task['completed'] = !$task['completed'];
            $_SESSION['flash_message'] = $task['completed']
                ? 'Task marcada como concluída!'
                : 'Task reaberta.';
            $_SESSION['flash_type'] = 'success';
            break;
        }
    }
    header('Location: index.php');
    exit;
}

if ($action === 'remove' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $filteredTasks = array_filter($_SESSION['tasks'], fn($task) => $task['id'] !== $id);
    if (count($filteredTasks) < count($_SESSION['tasks'])) {
        $_SESSION['tasks'] = array_values($filteredTasks);
        $_SESSION['flash_message'] = 'Task removida.';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: index.php');
    exit;
}

if ($action === 'clear_completed') {
    $_SESSION['tasks'] = array_values(array_filter(
        $_SESSION['tasks'],
        fn($task) => !$task['completed']
    ));
    $_SESSION['flash_message'] = 'Tasks concluídas removidas.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

// Recupera flash message
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Estatísticas
$totalTasks = count($_SESSION['tasks']);
$completedCount = count(array_filter($_SESSION['tasks'], fn($task) => $task['completed']));
$pendingCount = $totalTasks - $completedCount;

// Filtro
$filter = $_GET['filtro'] ?? 'todas';
if ($filter === 'concluidas') {
    $displayedTasks = array_filter($_SESSION['tasks'], fn($task) => $task['completed']);
} elseif ($filter === 'pendentes') {
    $displayedTasks = array_filter($_SESSION['tasks'], fn($task) => !$task['completed']);
} elseif ($filter !== 'todas' && in_array($filter, ['geral', 'trabalho', 'pessoal', 'estudo'])) {
    $displayedTasks = array_filter($_SESSION['tasks'], fn($task) => $task['category'] === $filter);
} else {
    $displayedTasks = $_SESSION['tasks'];
}

// Ordenação: não concluídas primeiro, depois por data (mais recente no topo)
usort($displayedTasks, function ($first, $second) {
    if ($first['completed'] !== $second['completed']) {
        return $first['completed'] ? 1 : -1;
    }
    return strcmp($second['created_at'], $first['created_at']);
});

function hh(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function emojiCategory(string $cat): string {
    return match ($cat) {
        'trabalho' => '💼',
        'pessoal'  => '🏠',
        'estudo'   => '📚',
        default    => '📋',
    };
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List — PHP Puro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f0f4f8;
            color: #334155;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        .app {
            max-width: 600px;
            width: 100%;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 2rem;
            color: #1e293b;
        }

        header p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .stat-card .numero {
            font-size: 1.6rem;
            font-weight: 800;
            display: block;
        }

        .stat-card .rotulo {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card.total .numero { color: #6366f1; }
        .stat-card.pendentes .numero { color: #f59e0b; }
        .stat-card.concluidas .numero { color: #10b981; }

        .mensagem {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .mensagem.sucesso { background: #d1fae5; color: #065f46; }
        .mensagem.erro    { background: #fee2e2; color: #991b1b; }

        .form-tarefa {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row input[type="text"] {
            flex: 1;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-row input[type="text"]:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        select {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
        }

        select:focus {
            outline: none;
            border-color: #6366f1;
        }

        .form-acoes {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-primary:hover { background: #4f46e5; }

        .btn-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .btn-danger:hover { background: #fee2e2; }

        .filtros {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filtro-link {
            padding: 6px 14px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
            transition: all 0.15s;
        }

        .filtro-link:hover { border-color: #6366f1; color: #6366f1; }
        .filtro-link.ativo  { background: #6366f1; color: white; border-color: #6366f1; }

        .lista-tasks { list-style: none; }

        .tarefa {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 14px 16px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .tarefa:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .tarefa.completed {
            opacity: 0.6;
        }

        .tarefa.completed .description {
            text-decoration: line-through;
            color: #94a3b8;
        }

        .tarefa .checkbox {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s;
            text-decoration: none;
            font-size: 0.75rem;
            color: transparent;
        }

        .tarefa .checkbox:hover { border-color: #6366f1; }

        .tarefa.completed .checkbox {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .tarefa .conteudo { flex: 1; min-width: 0; }

        .tarefa .description {
            font-size: 0.95rem;
            word-break: break-word;
        }

        .tarefa .meta {
            display: flex;
            gap: 8px;
            margin-top: 4px;
            font-size: 0.75rem;
            color: #94a3b8;
            align-items: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-geral    { background: #f1f5f9; color: #475569; }
        .badge-trabalho { background: #dbeafe; color: #1e40af; }
        .badge-pessoal  { background: #fce7f3; color: #9d174d; }
        .badge-estudo   { background: #fef3c7; color: #92400e; }

        .btn-remove {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            opacity: 0.4;
            transition: opacity 0.15s;
            padding: 4px 8px;
        }

        .btn-remove:hover { opacity: 1; }

        .vazio {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .vazio .icone { font-size: 3rem; display: block; margin-bottom: 12px; }

        .progresso {
            background: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            margin: 16px 0 8px;
            overflow: hidden;
        }

        .progresso .barra {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .progresso-label {
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="app">
    <header>
        <h1>📝 Minhas Tasks</h1>
        <p>Organize seu dia com PHP puro</p>
    </header>

    <!-- Estatísticas -->
    <div class="stats">
        <div class="stat-card total">
            <span class="numero"><?= $totalTasks ?></span>
            <span class="rotulo">Total</span>
        </div>
        <div class="stat-card pendentes">
            <span class="numero"><?= $pendingCount ?></span>
            <span class="rotulo">Pendentes</span>
        </div>
        <div class="stat-card concluidas">
            <span class="numero"><?= $completedCount ?></span>
            <span class="rotulo">Concluídas</span>
        </div>
    </div>

    <!-- Barra de progresso -->
    <?php if ($totalTasks > 0): ?>
    <?php $percentage = round(($completedCount / $totalTasks) * 100); ?>
    <div class="progresso">
        <div class="barra" style="width: <?= $percentage ?>%"></div>
    </div>
    <p class="progresso-label"><?= $percentage ?>% concluído</p>
    <?php endif; ?>

    <!-- Mensagem flash -->
    <?php if ($message): ?>
        <div class="mensagem <?= $messageType ?>"><?= hh($message) ?></div>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="form-tarefa">
        <form method="post" action="index.php">
            <input type="hidden" name="action" value='add'>
            <div class="form-row">
                <input type="text" name="description"
                       placeholder="O que você precisa fazer?" maxlength="200"
                       autocomplete="off" autofocus>
                <select name="category">
                    <option value="geral">Geral</option>
                    <option value="trabalho">Trabalho</option>
                    <option value="pessoal">Pessoal</option>
                    <option value="estudo">Estudo</option>
                </select>
            </div>
            <div class="form-acoes">
                <button type="submit" class="btn btn-primary">Adicionar Task</button>
                <?php if ($completedCount > 0): ?>
                    <a href="index.php?action=clear_completed" class="btn btn-danger"
                       onclick="return confirm('Remover todas as tasks concluídas?')">
                        Limpar Concluídas
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Filtros -->
    <div class="filtros">
        <?php
        $filters = [
            'todas'       => 'Todas',
            'pendentes'   => 'Pendentes',
            'concluidas'  => 'Concluídas',
            'geral'       => 'Geral',
            'trabalho'    => 'Trabalho',
            'pessoal'     => 'Pessoal',
            'estudo'      => 'Estudo',
        ];
        foreach ($filters as $filterValue => $label):
            $activeClass = ($filter === $filterValue) ? 'ativo' : '';
        ?>
            <a href="index.php?filtro=<?= $filterValue ?>" class="filtro-link <?= $activeClass ?>">
                <?= hh($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Lista de Tasks -->
    <?php if (empty($displayedTasks)): ?>
        <div class="vazio">
            <span class="icone">🎉</span>
            <p>Nenhuma tarefa encontrada. Adicione uma acima!</p>
        </div>
    <?php else: ?>
        <ul class="lista-tasks">
            <?php foreach ($displayedTasks as $task): ?>
            <?php $completedClass = $task['completed'] ? 'completed' : ''; ?>
            <li class="tarefa <?= $completedClass ?>">
                <a href="index.php?action=toggle&id=<?= $task['id'] ?>"
                   class="checkbox"
                   title="<?= $task['completed'] ? 'Reabrir' : 'Concluir' ?>">
                    ✓
                </a>
                <div class="conteudo">
                    <span class="description"><?= hh($task['description']) ?></span>
                    <div class="meta">
                        <span class="badge badge-<?= $task['category'] ?>">
                            <?= emojiCategory($task['category']) ?>
                            <?= ucfirst($task['category']) ?>
                        </span>
                        <span><?= hh($task['created_at']) ?></span>
                    </div>
                </div>
                <a href="index.php?action=remove&id=<?= $task['id'] ?>"
                   class="btn-remove"
                   title="Remover tarefa"
                   onclick="return confirm('Remover esta tarefa?')">
                    🗑️
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
```

---

## Como Executar

```bash
php -S localhost:8080 -t /caminho/para/projetos/todolist/
# Acesse http://localhost
```

---

## Funcionalidades

- Adicionar tarefa com categoria (geral, trabalho, pessoal, estudo)
- Marcar/desmarcar como concluída (toggle)
- Remover tarefa individual
- Limpar todas as concluídas de uma vez
- Filtrar por status ou categoria
- Estatísticas (total, pendentes, concluídas)
- Barra de progresso
- Ordenação: pendentes primeiro, depois por data
- Mensagens flash após cada ação
- Redirecionamento pós-POST (PRG pattern)

---

## Desafios Extras

### 1. Migrar para SQLite com PDO
Substitua `$_SESSION['tarefas']` por uma tabela `tarefas` no SQLite. Use PDO com prepared statements. Adicione coluna `prazo` (DATE) e `prioridade` (alta/média/baixa).

### 2. Ordenação por coluna
Adicione links clicáveis no cabeçalho para ordenar por data, categoria ou status.

### 3. Exportar tarefas
Adicione um botão para exportar a lista como arquivo CSV.

### 4. Prazo e notificações visuais
Adicione campo de data de prazo. Destaque tarefas próximas do vencimento (hoje ou amanhã) com cores diferentes.

---

## Conceitos Aplicados
- Superglobal `$_SESSION` para persistência
- Padrão PRG (Post-Redirect-Get) com flash messages
- Arrays e `array_filter`, `array_map`, `usort`
- Filtros com query string (`$_GET`)
- Validação de formulários
- Output seguro com `htmlspecialchars`
- Design responsivo com CSS Grid e Flexbox
