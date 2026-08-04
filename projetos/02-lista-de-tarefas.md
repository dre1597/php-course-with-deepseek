# Projeto 02: Lista de Tarefas (To-Do List)

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

// Inicializa lista de tarefas
if (!isset($_SESSION['tarefas'])) {
    $_SESSION['tarefas'] = [];
}

// Inicializa IDs automáticos
if (!isset($_SESSION['proximo_id'])) {
    $_SESSION['proximo_id'] = 1;
}

// Inicializa mensagem flash
$mensagem = '';
$tipoMensagem = '';

// ========== AÇÕES ==========
$acao = $_GET['acao'] ?? ($_POST['acao'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'adicionar') {
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'geral');

    if ($descricao === '') {
        $mensagem = 'A descrição da tarefa é obrigatória.';
        $tipoMensagem = 'erro';
    } elseif (strlen($descricao) > 200) {
        $mensagem = 'A tarefa deve ter no máximo 200 caracteres.';
        $tipoMensagem = 'erro';
    } elseif (!in_array($categoria, ['geral', 'trabalho', 'pessoal', 'estudo'])) {
        $mensagem = 'Categoria inválida.';
        $tipoMensagem = 'erro';
    } else {
        $_SESSION['tarefas'][] = [
            'id'            => $_SESSION['proximo_id']++,
            'descricao'     => $descricao,
            'categoria'     => $categoria,
            'concluida'     => false,
            'criada_em'     => date('Y-m-d H:i:s'),
        ];
        $mensagem = 'Tarefa adicionada com sucesso!';
        $tipoMensagem = 'sucesso';
    }
    // Redireciona para evitar reenvio
    $_SESSION['flash_mensagem'] = $mensagem;
    $_SESSION['flash_tipo'] = $tipoMensagem;
    header('Location: index.php');
    exit;
}

if ($acao === 'concluir' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    foreach ($_SESSION['tarefas'] as &$tarefa) {
        if ($tarefa['id'] === $id) {
            $tarefa['concluida'] = !$tarefa['concluida'];
            $_SESSION['flash_mensagem'] = $tarefa['concluida']
                ? 'Tarefa marcada como concluída!'
                : 'Tarefa reaberta.';
            $_SESSION['flash_tipo'] = 'sucesso';
            break;
        }
    }
    header('Location: index.php');
    exit;
}

if ($acao === 'remover' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $tarefasFiltradas = array_filter($_SESSION['tarefas'], fn($t) => $t['id'] !== $id);
    if (count($tarefasFiltradas) < count($_SESSION['tarefas'])) {
        $_SESSION['tarefas'] = array_values($tarefasFiltradas);
        $_SESSION['flash_mensagem'] = 'Tarefa removida.';
        $_SESSION['flash_tipo'] = 'sucesso';
    }
    header('Location: index.php');
    exit;
}

if ($acao === 'limpar_concluidas') {
    $_SESSION['tarefas'] = array_values(array_filter(
        $_SESSION['tarefas'],
        fn($t) => !$t['concluida']
    ));
    $_SESSION['flash_mensagem'] = 'Tarefas concluídas removidas.';
    $_SESSION['flash_tipo'] = 'sucesso';
    header('Location: index.php');
    exit;
}

// Recupera flash message
$mensagem = $_SESSION['flash_mensagem'] ?? '';
$tipoMensagem = $_SESSION['flash_tipo'] ?? '';
unset($_SESSION['flash_mensagem'], $_SESSION['flash_tipo']);

// Estatísticas
$totalTarefas = count($_SESSION['tarefas']);
$concluidas = count(array_filter($_SESSION['tarefas'], fn($t) => $t['concluida']));
$pendentes = $totalTarefas - $concluidas;

// Filtro
$filtro = $_GET['filtro'] ?? 'todas';
if ($filtro === 'concluidas') {
    $tarefasExibidas = array_filter($_SESSION['tarefas'], fn($t) => $t['concluida']);
} elseif ($filtro === 'pendentes') {
    $tarefasExibidas = array_filter($_SESSION['tarefas'], fn($t) => !$t['concluida']);
} elseif ($filtro !== 'todas' && in_array($filtro, ['geral', 'trabalho', 'pessoal', 'estudo'])) {
    $tarefasExibidas = array_filter($_SESSION['tarefas'], fn($t) => $t['categoria'] === $filtro);
} else {
    $tarefasExibidas = $_SESSION['tarefas'];
}

// Ordenação: não concluídas primeiro, depois por data (mais recente no topo)
usort($tarefasExibidas, function ($a, $b) {
    if ($a['concluida'] !== $b['concluida']) {
        return $a['concluida'] ? 1 : -1;
    }
    return strcmp($b['criada_em'], $a['criada_em']);
});

function hh(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function emojiCategoria(string $cat): string {
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

        .lista-tarefas { list-style: none; }

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

        .tarefa.concluida {
            opacity: 0.6;
        }

        .tarefa.concluida .descricao {
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

        .tarefa.concluida .checkbox {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .tarefa .conteudo { flex: 1; min-width: 0; }

        .tarefa .descricao {
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

        .btn-remover {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            opacity: 0.4;
            transition: opacity 0.15s;
            padding: 4px 8px;
        }

        .btn-remover:hover { opacity: 1; }

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
        <h1>📝 Minhas Tarefas</h1>
        <p>Organize seu dia com PHP puro</p>
    </header>

    <!-- Estatísticas -->
    <div class="stats">
        <div class="stat-card total">
            <span class="numero"><?= $totalTarefas ?></span>
            <span class="rotulo">Total</span>
        </div>
        <div class="stat-card pendentes">
            <span class="numero"><?= $pendentes ?></span>
            <span class="rotulo">Pendentes</span>
        </div>
        <div class="stat-card concluidas">
            <span class="numero"><?= $concluidas ?></span>
            <span class="rotulo">Concluídas</span>
        </div>
    </div>

    <!-- Barra de progresso -->
    <?php if ($totalTarefas > 0): ?>
    <?php $percentual = round(($concluidas / $totalTarefas) * 100); ?>
    <div class="progresso">
        <div class="barra" style="width: <?= $percentual ?>%"></div>
    </div>
    <p class="progresso-label"><?= $percentual ?>% concluído</p>
    <?php endif; ?>

    <!-- Mensagem flash -->
    <?php if ($mensagem): ?>
        <div class="mensagem <?= $tipoMensagem ?>"><?= hh($mensagem) ?></div>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="form-tarefa">
        <form method="post" action="index.php">
            <input type="hidden" name="acao" value="adicionar">
            <div class="form-row">
                <input type="text" name="descricao"
                       placeholder="O que você precisa fazer?" maxlength="200"
                       autocomplete="off" autofocus>
                <select name="categoria">
                    <option value="geral">Geral</option>
                    <option value="trabalho">Trabalho</option>
                    <option value="pessoal">Pessoal</option>
                    <option value="estudo">Estudo</option>
                </select>
            </div>
            <div class="form-acoes">
                <button type="submit" class="btn btn-primary">Adicionar Tarefa</button>
                <?php if ($concluidas > 0): ?>
                    <a href="index.php?acao=limpar_concluidas" class="btn btn-danger"
                       onclick="return confirm('Remover todas as tarefas concluídas?')">
                        Limpar Concluídas
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Filtros -->
    <div class="filtros">
        <?php
        $filtros = [
            'todas'       => 'Todas',
            'pendentes'   => 'Pendentes',
            'concluidas'  => 'Concluídas',
            'geral'       => 'Geral',
            'trabalho'    => 'Trabalho',
            'pessoal'     => 'Pessoal',
            'estudo'      => 'Estudo',
        ];
        foreach ($filtros as $valor => $rotulo):
            $classeAtivo = ($filtro === $valor) ? 'ativo' : '';
        ?>
            <a href="index.php?filtro=<?= $valor ?>" class="filtro-link <?= $classeAtivo ?>">
                <?= hh($rotulo) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Lista de Tarefas -->
    <?php if (empty($tarefasExibidas)): ?>
        <div class="vazio">
            <span class="icone">🎉</span>
            <p>Nenhuma tarefa encontrada. Adicione uma acima!</p>
        </div>
    <?php else: ?>
        <ul class="lista-tarefas">
            <?php foreach ($tarefasExibidas as $tarefa): ?>
            <?php $classeConcluida = $tarefa['concluida'] ? 'concluida' : ''; ?>
            <li class="tarefa <?= $classeConcluida ?>">
                <a href="index.php?acao=concluir&id=<?= $tarefa['id'] ?>"
                   class="checkbox"
                   title="<?= $tarefa['concluida'] ? 'Reabrir' : 'Concluir' ?>">
                    ✓
                </a>
                <div class="conteudo">
                    <span class="descricao"><?= hh($tarefa['descricao']) ?></span>
                    <div class="meta">
                        <span class="badge badge-<?= $tarefa['categoria'] ?>">
                            <?= emojiCategoria($tarefa['categoria']) ?>
                            <?= ucfirst($tarefa['categoria']) ?>
                        </span>
                        <span><?= hh($tarefa['criada_em']) ?></span>
                    </div>
                </div>
                <a href="index.php?acao=remover&id=<?= $tarefa['id'] ?>"
                   class="btn-remover"
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
</html>
```

---

## Como Executar

```bash
php -S localhost:8080 -t /caminho/para/projetos/todolist/
# Acesse http://localhost:8080
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
