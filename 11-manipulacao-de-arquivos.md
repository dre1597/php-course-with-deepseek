# Módulo 11: Manipulação de Arquivos

## Visão Geral

PHP oferece um conjunto robusto de funções para manipular arquivos no sistema de arquivos do servidor. Neste módulo, você aprenderá a abrir, ler, escrever, mover, renomear e deletar arquivos, além de trabalhar com uploads e streams.

---

## 1. Abrindo e Fechando Arquivos: `fopen()` e `fclose()`

A função `fopen()` é o ponto de partida para trabalhar com arquivos em PHP. Ela retorna um **resource** (ou `false` em caso de erro).

### Modos de abertura

| Modo | Descrição |
|------|-----------|
| `r`  | Leitura. Ponteiro no início. Arquivo deve existir. |
| `r+` | Leitura e escrita. Ponteiro no início. Arquivo deve existir. |
| `w`  | Escrita. Cria/trunca o arquivo. Ponteiro no início. |
| `w+` | Leitura e escrita. Cria/trunca o arquivo. |
| `a`  | Escrita. Cria se não existir. Ponteiro no final. |
| `a+` | Leitura e escrita. Cria se não existir. Ponteiro no final. |

```php
<?php
// Modo 'r' — somente leitura
$arquivo = fopen('dados.txt', 'r');
if ($arquivo === false) {
    die('Não foi possível abrir o arquivo.');
}
// Trabalha com o arquivo...
fclose($arquivo);

// Modo 'w' — escrita (sobrescreve)
$arquivo = fopen('log.txt', 'w');
fwrite($arquivo, "Linha 1\n");
fwrite($arquivo, "Linha 2\n");
fclose($arquivo);

// Modo 'a' — adiciona ao final
$arquivo = fopen('log.txt', 'a');
fwrite($arquivo, "Linha 3 (append)\n");
fclose($arquivo);

// Modo 'r+' — leitura e escrita (arquivo deve existir)
$arquivo = fopen('log.txt', 'r+');
$conteudo = fread($arquivo, 1024);
fwrite($arquivo, "\nAdicionado via r+\n");
fclose($arquivo);
```

### `fclose()` — Sempre feche o arquivo

```php
<?php
$arquivo = fopen('dados.txt', 'r');
// ... operações ...
fclose($arquivo); // libera o recurso e o lock
```

> 💡 **Dica:** Se você esquecer de chamar `fclose()`, o PHP fecha ao final da execução do script. Porém, é boa prática fechar explicitamente, ao trabalhar com locks.

---

## 2. Leitura de Arquivos

### `fread()` — Leitura por bytes

```php
<?php
$arquivo = fopen('texto.txt', 'r');
$conteudo = fread($arquivo, filesize('texto.txt'));
fclose($arquivo);
echo $conteudo;
```

### `fgets()` — Leitura linha por linha

```php
<?php
$arquivo = fopen('texto.txt', 'r');
while (($linha = fgets($arquivo)) !== false) {
    echo rtrim($linha) . "<br>\n";
}
fclose($arquivo);
```

### `fgetcsv()` — Leitura de CSV

```php
<?php
$arquivo = fopen('dados.csv', 'r');

// Lê o cabeçalho
$cabecalho = fgetcsv($arquivo);

// Lê cada linha como array associativo
while (($linha = fgetcsv($arquivo)) !== false) {
    $registro = array_combine($cabecalho, $linha);
    echo "Nome: {$registro['nome']}, Email: {$registro['email']}<br>\n";
}
fclose($arquivo);
```

Suponha que `dados.csv` contenha:
```
nome,email,idade
João,joao@email.com,28
Maria,maria@email.com,34
```

Saída:
```
Nome: João, Email: joao@email.com
Nome: Maria, Email: maria@email.com
```

### `fgetcsv()` com delimitador personalizado

```php
<?php
$arquivo = fopen('dados.tsv', 'r');
while (($linha = fgetcsv($arquivo, 0, "\t")) !== false) {
    print_r($linha);
}
fclose($arquivo);
```

### `fwrite()` — Escrita

```php
<?php
$arquivo = fopen('saida.txt', 'w');
fwrite($arquivo, "Primeira linha\n");
fwrite($arquivo, "Segunda linha\n");

$linhas = ['Linha 3', 'Linha 4', 'Linha 5'];
foreach ($linhas as $linha) {
    fwrite($arquivo, $linha . "\n");
}
fclose($arquivo);
```

---

## 3. `file_get_contents()` e `file_put_contents()`

Estas funções simplificam drasticamente a leitura e escrita de arquivos inteiros.

### `file_get_contents()` — Lê o arquivo inteiro em uma string

```php
<?php
// Leitura simples
$conteudo = file_get_contents('texto.txt');
if ($conteudo === false) {
    die('Erro ao ler o arquivo.');
}
echo nl2br($conteudo);

// Leitura de URL (se allow_url_fopen estiver habilitado)
$html = file_get_contents('https://www.example.com');

// Leitura com contexto de stream (headers customizados)
$contexto = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: PHP Script\r\n"
    ]
]);
$html = file_get_contents('https://api.example.com/dados', false, $contexto);
```

### `file_put_contents()` — Escreve uma string em um arquivo

```php
<?php
// Escrita simples (sobrescreve)
file_put_contents('arquivo.txt', 'Conteúdo do arquivo');

// Append (adiciona ao final)
file_put_contents('arquivo.txt', "\nMais conteúdo", FILE_APPEND);

// Com LOCK_EX para evitar concorrência
file_put_contents('arquivo.txt', 'Conteúdo seguro', LOCK_EX);

// Combinação de flags
file_put_contents('arquivo.txt', "Conteúdo\n", FILE_APPEND | LOCK_EX);

// Verificar retorno
$resultado = file_put_contents('dados.json', json_encode(['nome' => 'João']));
if ($resultado === false) {
    die('Erro ao escrever no arquivo.');
}
echo "{$resultado} bytes escritos.";
```

> 💡 **Dica:** `file_get_contents()` e `file_put_contents()` são atalhos convenientes. Para arquivos muito grandes, prefira `fopen()` + leitura incremental para não estourar a memória.

---

## 4. Verificações de Arquivos e Diretórios

```php
<?php
$caminho = '/var/www/html/index.php';

// Existe?
if (file_exists($caminho)) {
    echo "O caminho existe!<br>\n";
}

// É arquivo?
if (is_file($caminho)) {
    echo "É um arquivo!<br>\n";
}

// É diretório?
if (is_dir('/var/www/html')) {
    echo "É um diretório!<br>\n";
}

// Permissões
if (is_readable($caminho)) {
    echo "Tem permissão de leitura<br>\n";
}

if (is_writable($caminho)) {
    echo "Tem permissão de escrita<br>\n";
}

// Função utilitária: verificar arquivo antes de processar
function validarArquivo(string $caminho): bool {
    if (!file_exists($caminho)) {
        echo "Erro: arquivo '{$caminho}' não encontrado.<br>\n";
        return false;
    }
    if (!is_file($caminho)) {
        echo "Erro: '{$caminho}' não é um arquivo.<br>\n";
        return false;
    }
    if (!is_readable($caminho)) {
        echo "Erro: sem permissão de leitura.<br>\n";
        return false;
    }
    return true;
}
```

---

## 5. `file()` e `readfile()`

### `file()` — Lê o arquivo inteiro em um array (cada elemento = uma linha)

```php
<?php
$linhas = file('texto.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($linhas as $numero => $linha) {
    echo ($numero + 1) . ": {$linha}<br>\n";
}

// Contar linhas rapidamente
echo "Total de linhas: " . count(file('texto.txt'));
```

### `readfile()` — Lê e envia para o buffer de saída

```php
<?php
// Útil para forçar download de arquivos
$arquivo = 'documento.pdf';

if (file_exists($arquivo)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($arquivo) . '"');
    header('Content-Length: ' . filesize($arquivo));
    readfile($arquivo);
    exit;
}
```

---

## 6. Metadados de Arquivos

```php
<?php
$arquivo = 'texto.txt';

// Último acesso (timestamp Unix)
$acesso = fileatime($arquivo);
echo "Último acesso: " . date('d/m/Y H:i:s', $acesso) . "<br>\n";

// Última modificação
$modificacao = filemtime($arquivo);
echo "Última modificação: " . date('d/m/Y H:i:s', $modificacao) . "<br>\n";

// Tamanho em bytes
$tamanho = filesize($arquivo);
echo "Tamanho: {$tamanho} bytes<br>\n";

// Formatação amigável de tamanho
function formatarTamanho(int $bytes): string {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $unidades[$i];
}

echo "Tamanho formatado: " . formatarTamanho($tamanho) . "<br>\n";
```

---

## 7. Manipulando Arquivos: `unlink()`, `rename()`, `copy()`

### `unlink()` — Deletar arquivo

```php
<?php
$arquivo = 'temporario.txt';

if (file_exists($arquivo)) {
    if (unlink($arquivo)) {
        echo "Arquivo deletado com sucesso.";
    } else {
        echo "Erro ao deletar o arquivo.";
    }
}
```

> ⚠️ **Cuidado:** `unlink()` deleta o arquivo permanentemente. Não vai para uma lixeira. Sempre verifique antes de deletar.

### `rename()` — Renomear ou mover

```php
<?php
// Renomear
rename('antigo.txt', 'novo.txt');

// Mover para outro diretório
rename('documento.txt', 'backup/documento.txt');

// Mover com nome diferente
rename('rascunho.txt', 'finalizados/rascunho-2024.txt');
```

### `copy()` — Copiar arquivo

```php
<?php
$origem = 'foto-original.jpg';
$destino = 'backup/foto-copia.jpg';

if (copy($origem, $destino)) {
    echo "Arquivo copiado com sucesso.";
} else {
    echo "Erro ao copiar o arquivo.";
}
```

---

## 8. Diretórios: `mkdir()` e `rmdir()`

### `mkdir()` — Criar diretório

```php
<?php
// Criação simples
mkdir('nova-pasta');

// Criar recursivamente com permissões específicas
mkdir('projeto/src/controllers', 0755, true);

// Verificar antes de criar
$pasta = 'uploads/2024';
if (!is_dir($pasta)) {
    mkdir($pasta, 0755, true);
    echo "Diretório '{$pasta}' criado.<br>\n";
}
```

### `rmdir()` — Remover diretório (deve estar vazio)

```php
<?php
$pasta = 'temp';

if (is_dir($pasta)) {
    rmdir($pasta);
    echo "Diretório '{$pasta}' removido.<br>\n";
}

// Função para remover diretório com todo conteúdo
function removerDiretorio(string $caminho): bool {
    if (!is_dir($caminho)) {
        return false;
    }
    $itens = scandir($caminho);
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $caminhoCompleto = $caminho . '/' . $item;
        if (is_dir($caminhoCompleto)) {
            removerDiretorio($caminhoCompleto);
        } else {
            unlink($caminhoCompleto);
        }
    }
    return rmdir($caminho);
}
```

---

## 9. Listando Conteúdo: `scandir()` e `glob()`

### `scandir()` — Lista arquivos e diretórios

```php
<?php
$itens = scandir('/var/www/html');

echo "<ul>\n";
foreach ($itens as $item) {
    if ($item === '.' || $item === '..') {
        continue;
    }
    $tipo = is_dir($item) ? '[DIR]' : '[ARQ]';
    echo "<li>{$tipo} {$item}</li>\n";
}
echo "</ul>\n";

// Ordenação reversa
$itens = scandir('/var/www/html', SCANDIR_SORT_DESCENDING);
```

### `glob()` — Busca com padrão (wildcard)

```php
<?php
// Todos os arquivos .php no diretório
$arquivosPHP = glob('*.php');
print_r($arquivosPHP);

// Todos os arquivos .txt em subdiretórios (recursivo com **)
$arquivosTXT = glob('**/*.txt');

// Buscar múltiplos padrões
$imagens = glob('*.{jpg,jpeg,png,gif}', GLOB_BRACE);

foreach ($imagens as $imagem) {
    echo "<img src='{$imagem}' alt='Imagem'><br>\n";
}

// Exemplo prático: listar arquivos de um diretório de uploads
function listarUploads(string $diretorio): array {
    if (!is_dir($diretorio)) {
        return [];
    }
    return glob($diretorio . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
}
```

---

## 10. Informações de Caminho: `pathinfo()`, `basename()`, `dirname()`, `realpath()`

```php
<?php
$arquivo = '/var/www/html/imagens/foto-perfil.jpg';

// pathinfo() — informações completas do caminho
$info = pathinfo($arquivo);
echo "Diretório: {$info['dirname']}<br>\n";     // /var/www/html/imagens
echo "Nome base: {$info['basename']}<br>\n";    // foto-perfil.jpg
echo "Extensão: {$info['extension']}<br>\n";    // jpg
echo "Nome: {$info['filename']}<br>\n";         // foto-perfil

// Ou buscar partes específicas via constante
echo pathinfo($arquivo, PATHINFO_EXTENSION);    // jpg
echo pathinfo($arquivo, PATHINFO_FILENAME);     // foto-perfil
echo pathinfo($arquivo, PATHINFO_DIRNAME);      // /var/www/html/imagens
echo pathinfo($arquivo, PATHINFO_BASENAME);     // foto-perfil.jpg

// basename() — apenas o nome do arquivo
echo basename('/var/www/html/index.php');        // index.php
echo basename('/var/www/html/index.php', '.php'); // index (remove extensão)

// dirname() — apenas o diretório
echo dirname('/var/www/html/index.php');         // /var/www/html
echo dirname('/var/www/html');                   // /var/www

// realpath() — caminho absoluto resolvido (resolve . e .., symlinks)
echo realpath('./arquivo.txt');                  // /var/www/html/arquivo.txt
echo realpath('../../etc/passwd');               // /etc/passwd

// Exemplo prático: sanitizar nome de arquivo de upload
function sanitizarNomeArquivo(string $nomeOriginal): string {
    $info = pathinfo($nomeOriginal);
    $nomeSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
    $extensao = strtolower($info['extension'] ?? '');
    return $nomeSanitizado . '.' . $extensao;
}

echo sanitizarNomeArquivo('Minha Foto (2024)!!!.JPG'); // Minha_Foto__2024____.jpg
```

---

## 11. Upload de Arquivos com `$_FILES`

### Formulário HTML

```html
<!-- upload.html -->
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="arquivo" accept="image/*" required>
    <input type="text" name="descricao" placeholder="Descrição da imagem">
    <button type="submit">Enviar</button>
</form>
```

### Processamento do upload

```php
<?php
// upload.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.html');
    exit;
}

// Verifica se o arquivo foi enviado
if (!isset($_FILES['arquivo'])) {
    die('Nenhum arquivo enviado.');
}

$arquivo = $_FILES['arquivo'];

// Verifica erros do upload
if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    $mensagensErro = [
        UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o tamanho máximo definido no php.ini (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o tamanho máximo definido no formulário (MAX_FILE_SIZE).',
        UPLOAD_ERR_PARTIAL    => 'O upload foi feito parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
        UPLOAD_ERR_EXTENSION  => 'Uma extensão PHP interrompeu o upload.',
    ];
    $erro = $arquivo['error'];
    die('Erro no upload: ' . ($mensagensErro[$erro] ?? "Código de erro {$erro}"));
}

// Valida tamanho (limite: 5 MB)
$tamanhoMaximo = 5 * 1024 * 1024; // 5 MB
if ($arquivo['size'] > $tamanhoMaximo) {
    die('O arquivo é maior que 5 MB.');
}

// Valida tipo MIME usando finfo (mais seguro que $_FILES['type'])
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$tipoReal = finfo_file($finfo, $arquivo['tmp_name']);
finfo_close($finfo);

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($tipoReal, $tiposPermitidos)) {
    die("Tipo de arquivo '{$tipoReal}' não permitido.");
}

// Gera nome único para evitar conflitos
$extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
$nomeFinal = uniqid('img_', true) . '.' . $extensao;
$destino = __DIR__ . '/uploads/' . $nomeFinal;

// Garante que o diretório existe
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Move o arquivo do local temporário para o destino final
if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
    echo "Upload realizado com sucesso!<br>\n";
    echo "Nome original: {$arquivo['name']}<br>\n";
    echo "Tipo: {$tipoReal}<br>\n";
    echo "Tamanho: " . formatarTamanho($arquivo['size']) . "<br>\n";
    echo "Salvo como: {$nomeFinal}<br>\n";
} else {
    die('Erro ao mover o arquivo.');
}
```

> ⚠️ **Cuidado:** Nunca confie no `$_FILES['arquivo']['type']`, pois é enviado pelo cliente. Sempre valide o tipo real usando `finfo`.

### Upload de múltiplos arquivos

```php
<?php
// Formulário: <input type="file" name="fotos[]" multiple>

function uploadMultiplo(array $fotos, string $diretorioDestino, array $tiposPermitidos, int $tamanhoMaximo): array {
    $uploadados = [];

    for ($i = 0; $i < count($fotos['name']); $i++) {
        if ($fotos['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // pula arquivos com erro
        }

        if ($fotos['size'][$i] > $tamanhoMaximo) {
            continue;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoReal = finfo_file($finfo, $fotos['tmp_name'][$i]);
        finfo_close($finfo);

        if (!in_array($tipoReal, $tiposPermitidos)) {
            continue;
        }

        $extensao = pathinfo($fotos['name'][$i], PATHINFO_EXTENSION);
        $nomeFinal = uniqid('foto_') . '_' . time() . '.' . $extensao;
        $destino = $diretorioDestino . '/' . $nomeFinal;

        if (move_uploaded_file($fotos['tmp_name'][$i], $destino)) {
            $uploadados[] = [
                'nome_original' => $fotos['name'][$i],
                'nome_final'    => $nomeFinal,
                'tipo'          => $tipoReal,
                'tamanho'       => $fotos['size'][$i],
            ];
        }
    }

    return $uploadados;
}
```

### PHP Ini Settings Relevantes para Upload

```ini
; php.ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20
upload_tmp_dir = /tmp/php-uploads
```

> 💡 **Dica:** `post_max_size` deve ser maior que `upload_max_filesize`, pois o POST inclui outros campos do formulário além do arquivo.

---

## 12. Posicionamento no Arquivo: `fseek()`, `ftell()`, `rewind()`

```php
<?php
$arquivo = fopen('texto.txt', 'r');

// Posição atual (em bytes a partir do início)
$posicao = ftell($arquivo);
echo "Posição atual: {$posicao}<br>\n"; // 0

// Move para o byte 10
fseek($arquivo, 10);
echo "Posição após fseek: " . ftell($arquivo) . "<br>\n"; // 10

// Modos de fseek
// SEEK_SET — a partir do início (padrão)
fseek($arquivo, 5, SEEK_SET);

// SEEK_CUR — a partir da posição atual
fseek($arquivo, 20, SEEK_CUR); // +20 bytes da posição atual

// SEEK_END — a partir do final
fseek($arquivo, -1, SEEK_END); // último byte do arquivo

// rewind() — volta ao início (equivalente a fseek($arquivo, 0))
rewind($arquivo);

fclose($arquivo);

// Exemplo: ler os últimos N bytes de um arquivo
function lerUltimosBytes(string $caminho, int $bytes): string {
    $arquivo = fopen($caminho, 'r');
    fseek($arquivo, -$bytes, SEEK_END);
    $dados = fread($arquivo, $bytes);
    fclose($arquivo);
    return $dados;
}
```

---

## 13. Travas de Arquivo: `flock()`

```php
<?php
// Escrita segura com lock exclusivo
$arquivo = fopen('contador.txt', 'c+'); // 'c+' abre para leitura/escrita, cria se não existe

if (flock($arquivo, LOCK_EX)) { // Lock exclusivo
    $contador = (int) fread($arquivo, 1024);
    $contador++;

    rewind($arquivo);
    ftruncate($arquivo, 0); // limpa o arquivo
    fwrite($arquivo, (string) $contador);

    flock($arquivo, LOCK_UN); // Libera o lock
} else {
    echo "Não foi possível obter lock.<br>\n";
}

fclose($arquivo);
echo "Contador: {$contador}<br>\n";
```

### Tipos de Lock

```php
<?php
// LOCK_SH — Lock compartilhado (leitura). Vários processos podem obter simultaneamente.
flock($arquivo, LOCK_SH);

// LOCK_EX — Lock exclusivo (escrita). Apenas um processo por vez.
flock($arquivo, LOCK_EX);

// LOCK_UN — Libera o lock.
flock($arquivo, LOCK_UN);

// LOCK_NB — Non-blocking. Retorna na hora se não conseguir o lock.
if (!flock($arquivo, LOCK_EX | LOCK_NB)) {
    echo "Arquivo ocupado no momento.<br>\n";
}
```

> ⚠️ **Cuidado:** `flock()` funciona apenas em sistemas com bloqueio consultivo (advisory locking), como Linux e macOS. No Windows, o comportamento pode variar. `flock()` não funciona com alguns wrappers de stream remotos.

---

## 14. Streams e Wrappers

### `php://input` — Lê o corpo bruto da requisição

```php
<?php
// Útil para APIs que recebem JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['erro' => 'JSON inválido']));
}

echo "Nome recebido: " . ($dados['nome'] ?? 'não informado');
```

### `php://output` — Escreve na saída

```php
<?php
$saida = fopen('php://output', 'w');
fputcsv($saida, ['Nome', 'Email', 'Idade']);
fputcsv($saida, ['João', 'joao@email.com', '28']);
fputcsv($saida, ['Maria', 'maria@email.com', '34']);
fclose($saida);
// Isso gera CSV na resposta HTTP
```

### `php://memory` e `php://temp` — Arquivos em memória

```php
<?php
// php://memory — armazena tudo em RAM
$memoria = fopen('php://memory', 'r+');
fwrite($memoria, "Dados temporários em memória\n");
fwrite($memoria, "Nada é escrito em disco\n");

rewind($memoria);
echo fread($memoria, 1024);
fclose($memoria);

// php://temp — armazena em RAM até 2MB, depois usa disco
$temp = fopen('php://temp', 'r+');
for ($i = 0; $i < 1000; $i++) {
    fwrite($temp, "Linha {$i}\n");
}
rewind($temp);
echo stream_get_contents($temp);
fclose($temp);
```

### Wrappers: lendo arquivos de diferentes fontes

```php
<?php
// Arquivos locais
$local = file_get_contents('/caminho/arquivo.txt');

// URLs HTTP (se allow_url_fopen = On)
$remoto = file_get_contents('https://jsonplaceholder.typicode.com/todos/1');

// FTP
// $ftp = file_get_contents('ftp://user:senha@servidor/arquivo.txt');

// Leitura de stdin (entrada padrão do terminal)
$stdin = file_get_contents('php://stdin');

// Dados enviados via POST tradicional
$post = file_get_contents('php://input');
```

---

## 15. Exemplo Prático: Sistema de Log

```php
<?php
class Logger {
    private string $arquivo;

    public function __construct(string $arquivo) {
        $this->arquivo = $arquivo;
    }

    public function log(string $nivel, string $mensagem, array $contexto = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextoStr = !empty($contexto) ? ' ' . json_encode($contexto) : '';
        $linha = "[{$timestamp}] {$nivel}: {$mensagem}{$contextoStr}\n";
        file_put_contents($this->arquivo, $linha, FILE_APPEND | LOCK_EX);
    }

    public function info(string $mensagem, array $contexto = []): void {
        $this->log('INFO', $mensagem, $contexto);
    }

    public function erro(string $mensagem, array $contexto = []): void {
        $this->log('ERROR', $mensagem, $contexto);
    }

    public function ler(int $linhas = 50, string $nivel = null): array {
        $logs = [];
        $arquivo = fopen($this->arquivo, 'r');
        if ($arquivo === false) {
            return $logs;
        }

        while (($linha = fgets($arquivo)) !== false) {
            if ($nivel !== null && !str_contains($linha, $nivel . ':')) {
                continue;
            }
            $logs[] = rtrim($linha);
        }
        fclose($arquivo);

        return array_slice($logs, -$linhas);
    }

    public function limpar(): bool {
        return file_put_contents($this->arquivo, '') !== false;
    }
}

// Uso
$logger = new Logger(__DIR__ . '/app.log');
$logger->info('Sistema iniciado', ['versao' => '2.0']);
$logger->erro('Falha na conexão com banco', ['erro' => 'timeout']);
print_r($logger->ler(10, 'ERROR'));
```

---

## 16. `request_parse_body()` (PHP 8.4+)

> **PHP 8.4+**

A nova função `request_parse_body()` permite processar o corpo da requisição de forma programática, útil para APIs que recebem dados em formatos como JSON.

```php
<?php
// PHP 8.4+ — alternativa a $_POST para APIs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = request_parse_body();

    // Para JSON, combine com php://input
    $tipoConteudo = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($tipoConteudo, 'application/json')) {
        $json = file_get_contents('php://input');
        $resultado = json_decode($json, true);
    }

    print_r($resultado);
}
```

---

## 📚 Referências

- [PHP: Funções de Sistema de Arquivos](https://www.php.net/manual/pt_BR/book.filesystem.php)
- [PHP: fopen](https://www.php.net/manual/pt_BR/function.fopen.php)
- [PHP: manipulando uploads de arquivos](https://www.php.net/manual/pt_BR/features.file-upload.php)
- [PHP: finfo — Fileinfo](https://www.php.net/manual/pt_BR/book.fileinfo.php)
- [PHP: Streams](https://www.php.net/manual/pt_BR/book.stream.php)
- [PHP: flock](https://www.php.net/manual/pt_BR/function.flock.php)
