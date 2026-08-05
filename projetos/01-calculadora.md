# Projeto 01: Calculadora Web

## Objetivo

Criar uma calculadora web completa em PHP puro, com formulário HTML e processamento no mesmo arquivo, validação de entradas, tratamento de erros e histórico via sessão.

## Estrutura de Arquivos

```
projetos/calculadora/
    index.php
```

Apenas um arquivo! Toda a lógica e o HTML ficam no `index.php`. Simples, didático e funcional.

---

## Código Completo

```php
<?php
// ==========================================
// index.php — Calculadora Web com Histórico
// ==========================================
session_start();

// Inicializa histórico na sessão
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

// Inicializa variáveis
$result    = '';
$error         = '';
$number1      = $_POST['number1'] ?? '';
$number2      = $_POST['number2'] ?? '';
$operation     = $_POST['operation'] ?? 'add';
$expression    = '';

// Adicionar ao histórico via POST
$historyAction = $_POST['history_action'] ?? '';

// Reutilizar cálculo do histórico
if ($historyAction === 'reuse' && isset($_POST['history_index'])) {
    $index = (int) $_POST['history_index'];
    if (isset($_SESSION['history'][$index])) {
        $historyItem = $_SESSION['history'][$index];
        $number1  = $historyItem['number1'];
        $number2  = $historyItem['number2'];
        $operation = $historyItem['operation'];
        // Força recalculate
        $_POST['calculate'] = '1';
    }
}

// Limpar histórico
if ($historyAction === 'clear') {
    $_SESSION['history'] = [];
}

// Processa o cálculo
if (isset($_POST['calculate'])) {
    $number1 = trim($_POST['number1'] ?? '');
    $number2 = trim($_POST['number2'] ?? '');
    $operation = $_POST['operation'] ?? 'add';

    // Validações
    if ($number1 === '' || $number2 === '') {
        $error = 'Preencha ambos os números.';
    } elseif (!is_numeric($number1) || !is_numeric($number2)) {
        $error = 'Informe apenas valores numéricos válidos.';
    } else {
        $n1 = (float) $number1;
        $n2 = (float) $number2;

        $result = match ($operation) {
            'add'         => $n1 + $n2,
            'subtract'    => $n1 - $n2,
            'multiply'    => $n1 * $n2,
            'divide'      => ($n2 != 0) ? $n1 / $n2 : null,
            'mod'         => ($n2 != 0) ? $n1 % $n2 : null,
            'power'       => $n1 ** $n2,
            default       => null,
        };

        if ($result === null) {
            $error = match ($operation) {
                'divide' => 'Divisão por zero não é permitida.',
                'mod'    => 'Módulo por zero não é permitido.',
                default  => 'Operação inválida.',
            };
        } else {
            // Formata o resultado
            if (is_float($result)) {
                $result = rtrim(rtrim(number_format($result, 10, '.', ''), '0'), '.');
            }

            // Monta expressão
            $symbols = [
                'add'       => '+',
                'subtract'  => '-',
                'multiply'  => '×',
                'divide'    => '÷',
                'mod'       => '%',
                'power'     => '^',
            ];
            $symbol = $symbols[$operation] ?? '?';
            $expression = "{$n1} {$symbol} {$n2} = {$result}";

            // Salva no histórico
            $historyEntry = [
                'number1'    => $n1,
                'number2'    => $n2,
                'operation'   => $operation,
                'symbol'    => $symbol,
                'result'    => $result,
                'expression'=> $expression,
                'time'      => date('H:i:s'),
            ];
            array_unshift($_SESSION['history'], $historyEntry);

            if (count($_SESSION['history']) > 20) {
                $_SESSION['history'] = array_slice($_SESSION['history'], 0, 20);
            }
        }
    }
}

// Para exibição no HTML
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora Web — PHP Puro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 550px;
            width: 100%;
            padding: 32px;
        }

        h1 {
            text-align: center;
            color: #4a1d96;
            font-size: 1.8rem;
            margin-bottom: 4px;
        }

        .subtitulo {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 24px;
        }

        .calculadora {
            background: #f8f7ff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .campo {
            margin-bottom: 16px;
        }

        .campo label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .campo input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0d8f0;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: border-color 0.2s;
            font-family: 'Courier New', monospace;
        }

        .campo input[type="text"]:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .operacoes {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .operacoes label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 2px solid #e0d8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.15s;
            text-align: center;
        }

        .operacoes label:hover {
            background: #ede9fe;
        }

        .operacoes input[type="radio"] {
            display: none;
        }

        .operacoes input[type="radio"]:checked + span {
            color: #7c3aed;
        }

        .operacoes input[type="radio"]:checked + .rotulo-operacao {
            background: #ede9fe;
            border-color: #7c3aed;
            color: #7c3aed;
        }

        .btn-calculate {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.2s;
        }

        .btn-calculate:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5);
        }

        .btn-calculate:active {
            transform: translateY(0);
        }

        .resultado-box {
            background: linear-gradient(135deg, #ede9fe 0%, #fae8ff 100%);
            border: 2px solid #d8b4fe;
            border-radius: 10px;
            padding: 16px;
            margin-top: 16px;
            text-align: center;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .resultado-box .expressao {
            font-size: 1rem;
            color: #555;
            margin-bottom: 4px;
        }

        .resultado-box .valor {
            font-size: 2.2rem;
            font-weight: 800;
            color: #4a1d96;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        .erro-box {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 10px;
            padding: 14px;
            margin-top: 16px;
            text-align: center;
            color: #dc2626;
            font-weight: 600;
        }

        .historico {
            margin-top: 24px;
        }

        .historico h3 {
            color: #555;
            font-size: 1rem;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-limpar {
            background: none;
            border: 1px solid #ddd;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            color: #888;
        }

        .btn-limpar:hover {
            background: #f5f5f5;
            color: #dc2626;
            border-color: #dc2626;
        }

        .lista-historico {
            list-style: none;
        }

        .item-historico {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #fafafa;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #eee;
        }

        .item-historico .expressao-historico {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .item-historico .resultado-historico {
            font-weight: 700;
            color: #7c3aed;
            font-family: 'Courier New', monospace;
        }

        .item-historico .detalhes {
            color: #999;
            font-size: 0.75rem;
            display: block;
        }

        .btn-reusar {
            background: #ede9fe;
            color: #7c3aed;
            border: none;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .btn-reusar:hover {
            background: #ddd6fe;
        }

        .vazio {
            text-align: center;
            color: #aaa;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Calculadora Web</h1>
    <p class="subtitulo">PHP Puro &mdash; operações básicas com histórico</p>

    <div class="calculadora">
        <form method="post" action="index.php">
            <div class="campo">
                <label for="number1">Primeiro número</label>
                <input type="text" id="number1" name="number1"
                       value="<?= h($number1) ?>" placeholder="Ex: 42.5" autocomplete="off">
            </div>

            <div class="campo">
                <label for="number2">Segundo número</label>
                <input type="text" id="number2" name="number2"
                       value="<?= h($number2) ?>" placeholder="Ex: 8" autocomplete="off">
            </div>

            <label style="font-weight:600; font-size:0.9rem; margin-bottom:8px; display:block;">Operação</label>
            <div class="operacoes">
                <?php
                $ops = [
                    'add'       => '+',
                    'subtract'  => '−',
                    'multiply'  => '×',
                    'divide'    => '÷',
                    'mod'       => '%',
                    'power'     => '^',
                ];
                foreach ($ops as $opValue => $opSymbol):
                    $checked = ($operation === $opValue) ? 'checked' : ''; ?>
                    <label>
                        <input type="radio" name="operation" value="<?= $opValue ?>" <?= $checked ?>>
                        <span><?= $opSymbol ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="calculate" value="1" class="btn-calculate">Calcular</button>
        </form>

        <?php if ($error): ?>
            <div class="erro-box"><?= h($error) ?></div>
        <?php elseif ($result !== '' && $result !== null): ?>
            <div class="resultado-box">
                <?php if ($expression): ?>
                    <div class="expressao"><?= h($expression) ?></div>
            <?php endif; ?>
    </div>
        <?php endif; ?>
    </div>

    <div class="historico">
        <h3>
            Histórico
            <?php if (!empty($_SESSION['history'])): ?>
                <form method="post" style="display:inline">
                    <input type="hidden" name="history_action" value="clear">
                    <button type="submit" class="btn-limpar">Limpar tudo</button>
                </form>
            <?php endif; ?>
        </h3>

        <?php if (empty($_SESSION['history'])): ?>
            <p class="vazio">Nenhum cálculo realizado ainda.</p>
        <?php else: ?>
            <ul class="lista-historico">
                <?php foreach ($_SESSION['history'] as $i => $item): ?>
                <li class="item-historico">
                    <div>
                        <span class="expressao-historico"><?= h($item['expression']) ?></span>
                        <span class="detalhes"><?= h($item['time']) ?></span>
                    </div>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="history_action" value="reuse">
                        <input type="hidden" name="history_index" value="<?= $i ?>">
                        <button type="submit" class="btn-reusar">Reusar</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
</body>
</h
```

---

## Como Executar

```bash
# Coloque o arquivo em um diretório acessível pelo servidor web
mkdir -p /var/www/html/calculadora
cp index.php /var/www/html/calculadora/

# Ou use o servidor embutido do PHP
php -S localhost:8080 -t /var/www/html/calculadora/
# Acesse http://localhost:
```

---

## Desafios Extras

### 1. Mais operações
Adicione raiz quadrada (`sqrt`), logaritmo, seno, cosseno, tangente. Adapte a interface para operações de um único operando.

### 2. Persistir histórico em arquivo
Substitua o armazenamento em sessão por um arquivo JSON. Assim o histórico sobrevive mesmo após fechar o navegador.

### 3. Temas (claro/escuro)
Use um cookie para armazenar a preferência de tema e aplique classes CSS condicionais.

### 4. Atalhos de teclado
Adicione JavaScript para permitir digitar números e operadores (ex: `42*8` e Enter para calculate).

---

## Conceitos Aplicados
- Formulários HTML com `$_POST`
- Estrutura `match` do PHP 8
- Validação com `is_numeric`
- Arrays e manipulação com `array_unshift`, `array_slice`
- Sessions (`$_SESSION`)
- Output seguro com `htmlspecialchars`
- CSS sem dependências externas
