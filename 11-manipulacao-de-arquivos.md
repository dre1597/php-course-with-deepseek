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
$file = fopen('dados.txt', 'r');
if ($file === false) {
    die('Não foi possível abrir o arquivo.');
}
// Trabalha com o arquivo...
fclose($file);

// Modo 'w' — escrita (sobrescreve)
$file = fopen('log.txt', 'w');
fwrite($file, "Linha 1\n");
fwrite($file, "Linha 2\n");
fclose($file);

// Modo 'a' — adiciona ao final
$file = fopen('log.txt', 'a');
fwrite($file, "Linha 3 (append)\n");
fclose($file);

// Modo 'r+' — leitura e escrita (arquivo deve existir)
$file = fopen('log.txt', 'r+');
$content = fread($file, 1024);
fwrite($file, "\nAdicionado via r+\n");
fclose($fi
```

### `fclose()` — Sempre feche o arquivo

```php
<?php
$file = fopen('dados.txt', 'r');
// ... operações ...
fclose($file); // libera o recurso e o 
```

> 💡 **Dica:** Se você esquecer de chamar `fclose()`, o PHP fecha ao final da execução do script. Porém, é boa prática fechar explicitamente, ao trabalhar com locks.

---

## 2. Leitura de Arquivos

### `fread()` — Leitura por bytes

```php
<?php
$file = fopen('texto.txt', 'r');
$content = fread($file, filesize('texto.txt'));
fclose($file);
echo $content
```

### `fgets()` — Leitura linha por linha

```php
<?php
$file = fopen('texto.txt', 'r');
while (($line = fgets($file)) !== false) {
    echo rtrim($line) . "<br>\n";
}
fclose($fi
```

### `fgetcsv()` — Leitura de CSV

```php
<?php
$file = fopen('dados.csv', 'r');

$header = fgetcsv($file);

while (($line = fgetcsv($file)) !== false) {
    $record = array_combine($header, $line);
    echo "Nome: {$record['name']}, Email: {$record['email']}<br>\n";
}
fclose($fi
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
$file = fopen('dados.tsv', 'r');
while (($line = fgetcsv($file, 0, "\t")) !== false) {
    print_r($line);
}
fclose($fi
```

### `fwrite()` — Escrita

```php
<?php
$file = fopen('saida.txt', 'w');
fwrite($file, "Primeira linha\n");
fwrite($file, "Segunda linha\n");

$lines = ['Linha 3', 'Linha 4', 'Linha 5'];
foreach ($lines as $line) {
    fwrite($file, $line . "\n");
}
fclose($fi
```

---

## 3. `file_get_contents()` e `file_put_contents()`

Estas funções simplificam drasticamente a leitura e escrita de arquivos inteiros.

### `file_get_contents()` — Lê o arquivo inteiro em uma string

```php
<?php
// Leitura simples
$content = file_get_contents('texto.txt');
if ($content === false) {
    die('Erro ao ler o arquivo.');
}
echo nl2br($content);

// Leitura de URL (se allow_url_fopen estiver habilitado)
$html = file_get_contents('https://www.example.com');

// Leitura com contexto de stream (headers customizados)
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: PHP Script\r\n"
    ]
]);
$html = file_get_contents('https://api.example.com/dados', false, $conte
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
$result = file_put_contents('dados.json', json_encode(['name' => 'João']));
if ($result === false) {
    die('Erro ao escrever no arquivo.');
}
echo "{$result} bytes escrito
```

> 💡 **Dica:** `file_get_contents()` e `file_put_contents()` são atalhos convenientes. Para arquivos muito grandes, prefira `fopen()` + leitura incremental para não estourar a memória.

---

## 4. Verificações de Arquivos e Diretórios

```php
<?php
$path = '/var/www/html/index.php';

// Existe?
if (file_exists($path)) {
    echo "O caminho existe!<br>\n";
}

// É arquivo?
if (is_file($path)) {
    echo "É um arquivo!<br>\n";
}

// É diretório?
if (is_dir('/var/www/html')) {
    echo "É um diretório!<br>\n";
}

// Permissões
if (is_readable($path)) {
    echo "Tem permissão de leitura<br>\n";
}

if (is_writable($path)) {
    echo "Tem permissão de escrita<br>\n";
}

// Função utilitária: check arquivo antes de process
function validateFile(string $path): bool {
    if (!file_exists($path)) {
        echo "Erro: arquivo '{$path}' não encontrado.<br>\n";
        return false;
    }
    if (!is_file($path)) {
        echo "Erro: '{$path}' não é um arquivo.<br>\n";
        return false;
    }
    if (!is_readable($path)) {
        echo "Erro: sem permissão de leitura.<br>\n";
        return false;
    }
    return tru
```

---

## 5. `file()` e `readfile()`

### `file()` — Lê o arquivo inteiro em um array (cada elemento = uma linha)

```php
<?php
$lines = file('texto.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $number => $line) {
    echo ($number + 1) . ": {$line}<br>\n";
}

// Contar linhas rapidamente
echo "Total de linhas: " . count(file('texto.txt
```

### `readfile()` — Lê e envia para o buffer de saída

```php
<?php
// Útil para forçar download de arquivos
$file = 'documento.pdf';

if (file_exists($file)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exi
```

---

## 6. Metadados de Arquivos

```php
<?php
$file = 'texto.txt';

// Último acesso (timestamp Unix)
$accessed = fileatime($file);
echo "Último acesso: " . date('d/m/Y H:i:s', $accessed) . "<br>\n";

// Última modificação
$modified = filemtime($file);
echo "Última modificação: " . date('d/m/Y H:i:s', $modified) . "<br>\n";

// Tamanho em bytes
$fileSize = filesize($file);
echo "Tamanho: {$fileSize} bytes<br>\n";

// Formatação amigável de tamanho
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

echo "Tamanho formatado: " . formatFileSize($fileSize) . "<br>

```

---

## 7. Manipulando Arquivos: `unlink()`, `rename()`, `copy()`

### `unlink()` — Deletar arquivo

```php
<?php
$file = 'temporario.txt';

if (file_exists($file)) {
    if (unlink($file)) {
        echo "Arquivo deletado com sucesso.";
    } else {
        echo "Erro ao deletar o arquivo.";
   
```

> ⚠️ **Cuidado:** `unlink()` deleta o arquivo permanentemente. Não vai para uma lixeira. Sempre verifique antes de deletar.

### `rename()` — Renomear ou mover

```php
<?php
// Renomear
rename('antigo.txt', 'novo.txt');

// Mover para outro diretório
rename('documento.txt', 'backup/documento.txt');

// Mover com name diferente
rename('rascunho.txt', 'finalizados/rascunho-2024.tx
```

### `copy()` — Copiar arquivo

```php
<?php
$source = 'foto-original.jpg';
$destination = 'backup/foto-copia.jpg';

if (copy($source, $destination)) {
    echo "Arquivo copiado com sucesso.";
} else {
    echo "Erro ao copiar o arquivo.
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
$directory = 'uploads/2024';
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
    echo "Diretório '{$directory}' criado.<br>\n
```

### `rmdir()` — Remover diretório (deve estar vazio)

```php
<?php
$directory = 'temp';

if (is_dir($directory)) {
    rmdir($directory);
    echo "Diretório '{$directory}' removido.<br>\n";
}

// Função para remover diretório com todo conteúdo
function removeDirectory(string $path): bool {
    if (!is_dir($path)) {
        return false;
    }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            removeDirectory($fullPath);
        } else {
            unlink($fullPath);
        }
    }
    return rmdir($path
```

---

## 9. Listando Conteúdo: `scandir()` e `glob()`

### `scandir()` — Lista arquivos e diretórios

```php
<?php
$items = scandir('/var/www/html');

echo "<ul>\n";
foreach ($items as $item) {
    if ($item === '.' || $item === '..') {
        continue;
    }
    $type = is_dir($item) ? '[DIR]' : '[ARQ]';
    echo "<li>{$type} {$item}</li>\n";
}
echo "</ul>\n";

// Ordenação reversa
$items = scandir('/var/www/html', SCANDIR_SORT_DESCENDI
```

### `glob()` — Busca com padrão (wildcard)

```php
<?php
// Todos os arquivos .php no diretório
$filesPHP = glob('*.php');
print_r($filesPHP);

// Todos os arquivos .txt em subdiretórios (recursivo com **)
$filesTXT = glob('**/*.txt');

// Buscar múltiplos padrões
$images = glob('*.{jpg,jpeg,png,gif}', GLOB_BRACE);

foreach ($images as $image) {
    echo "<img src='{$image}' alt='Imagem'><br>\n";
}

// Exemplo prático: list arquivos de um diretório de uploads
function listUploads(string $dir): array {
    if (!is_dir($dir)) {
        return [];
    }
    return glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE
```

---

## 10. Informações de Caminho: `pathinfo()`, `basename()`, `dirname()`, `realpath()`

```php
<?php
$file = '/var/www/html/imagens/foto-perfil.jpg';

// pathinfo() — informações completas do caminho
$info = pathinfo($file);
echo "Diretório: {$info['dirname']}<br>\n";     // /var/www/html/imagens
echo "Nome base: {$info['basename']}<br>\n";    // foto-perfil.jpg
echo "Extensão: {$info['extension']}<br>\n";    // jpg
echo "Nome: {$info['filename']}<br>\n";         // foto-perfil

// Ou buscar partes específicas via constante
echo pathinfo($file, PATHINFO_EXTENSION);    // jpg
echo pathinfo($file, PATHINFO_FILENAME);     // foto-perfil
echo pathinfo($file, PATHINFO_DIRNAME);      // /var/www/html/imagens
echo pathinfo($file, PATHINFO_BASENAME);     // foto-perfil.jpg

// basename() — apenas o name do arquivo
echo basename('/var/www/html/index.php');        // index.php
echo basename('/var/www/html/index.php', '.php'); // index (remove extensão)

// dirname() — apenas o diretório
echo dirname('/var/www/html/index.php');         // /var/www/html
echo dirname('/var/www/html');                   // /var/www

// realpath() — caminho absoluto resolvido (resolve . e .., symlinks)
echo realpath('./arquivo.txt');                  // /var/www/html/arquivo.txt
echo realpath('../../etc/passwd');               // /etc/passwd

// Exemplo prático: sanitize name de arquivo de upload
function sanitizeFileName(string $originalName): string {
    $info = pathinfo($originalName);
    $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
    $extension = strtolower($info['extension'] ?? '');
    return $sanitizedName . '.' . $extension;
}

echo sanitizeFileName('Minha Foto (2024)!!!.JPG'); // Minha_Foto__2024____
```

---

## 11. Upload de Arquivos com `$_FILES`

### Formulário HTML

```html
<!-- upload.html -->
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="arquivo" accept="image/*" required>
    <input type="text" name="description" placeholder="Descrição da imagem">
    <button type="submit">Enviar</button>
</form
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

$file = $_FILES['arquivo'];

// Verifica erros do upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o tamanho máximo definido no php.ini (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o tamanho máximo definido no formulário (MAX_FILE_SIZE).',
        UPLOAD_ERR_PARTIAL    => 'O upload foi feito parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
        UPLOAD_ERR_EXTENSION  => 'Uma extensão PHP interrompeu o upload.',
    ];
    $error = $file['error'];
    die('Erro no upload: ' . ($errorMessages[$error] ?? "Código de erro {$error}"));
}

// Valida tamanho (limite: 5 MB)
$maxFileSize = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxFileSize) {
    die('O arquivo é maior que 5 MB.');
}

// Valida tipo MIME usando finfo (mais seguro que $_FILES['type'])
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($realType, $allowedTypes)) {
    die("Tipo de arquivo '{$realType}' não permitido.");
}

// Gera name único para evitar conflitos
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('img_', true) . '.' . $extension;
$destination = __DIR__ . '/uploads/' . $fileName;

// Garante que o diretório existe
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Move o arquivo do local temporário para o destino final
if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo "Upload realizado com sucesso!<br>\n";
    echo "Nome original: {$file['name']}<br>\n";
    echo "Tipo: {$realType}<br>\n";
    echo "Tamanho: " . formatFileSize($file['size']) . "<br>\n";
    echo "Salvo como: {$fileName}<br>\n";
} else {
    die('Erro ao mover o arquivo.'
```

> ⚠️ **Cuidado:** Nunca confie no `$_FILES['arquivo']['type']`, pois é enviado pelo cliente. Sempre valide o tipo real usando `finfo`.

### Upload de múltiplos arquivos

```php
<?php
// Formulário: <input type="file" name="fotos[]" multiple>

function uploadMultiple(array $photos, string $targetDir, array $allowedTypes, int $maxFileSize): array {
    $uploadedFiles = [];

    for ($i = 0; $i < count($photos['name']); $i++) {
        if ($photos['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // pula arquivos com erro
        }

        if ($photos['size'][$i] > $maxFileSize) {
            continue;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realType = finfo_file($finfo, $photos['tmp_name'][$i]);
        finfo_close($finfo);

        if (!in_array($realType, $allowedTypes)) {
            continue;
        }

        $extension = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
        $fileName = uniqid('foto_') . '_' . time() . '.' . $extension;
        $destination = $targetDir . '/' . $fileName;

        if (move_uploaded_file($photos['tmp_name'][$i], $destination)) {
            $uploadedFiles[] = [
                'original_name' => $photos['name'][$i],
                'final_name'    => $fileName,
                'tipo'          => $realType,
                'tamanho'       => $photos['size'][$i],
            ];
        }
    }

    return $uploadedFile
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
$file = fopen('texto.txt', 'r');

// Posição atual (em bytes a partir do início)
$position = ftell($file);
echo "Posição atual: {$position}<br>\n"; // 0

// Move para o byte 10
fseek($file, 10);
echo "Posição após fseek: " . ftell($file) . "<br>\n"; // 10

// Modos de fseek
// SEEK_SET — a partir do início (padrão)
fseek($file, 5, SEEK_SET);

// SEEK_CUR — a partir da posição atual
fseek($file, 20, SEEK_CUR); // +20 bytes da posição atual

// SEEK_END — a partir do final
fseek($file, -1, SEEK_END); // último byte do arquivo

// rewind() — volta ao início (equivalente a fseek($file, 0))
rewind($file);

fclose($file);

// Exemplo: ler os últimos N bytes de um arquivo
function readLastBytes(string $path, int $bytes): string {
    $file = fopen($path, 'r');
    fseek($file, -$bytes, SEEK_END);
    $data = fread($file, $bytes);
    fclose($file);
    return $dat
```

---

## 13. Travas de Arquivo: `flock()`

```php
<?php
// Escrita segura com lock exclusivo
$file = fopen('contador.txt', 'c+'); // 'c+' abre para leitura/escrita, cria se não existe

if (flock($file, LOCK_EX)) { // Lock exclusivo
    $counter = (int) fread($file, 1024);
    $counter++;

    rewind($file);
    ftruncate($file, 0); // limpa o arquivo
    fwrite($file, (string) $counter);

    flock($file, LOCK_UN); // Libera o lock
} else {
    echo "Não foi possível get lock.<br>\n";
}

fclose($file);
echo "Contador: {$counter}<br>

```

### Tipos de Lock

```php
<?php
// LOCK_SH — Lock compartilhado (leitura). Vários processos podem get simultaneamente.
flock($file, LOCK_SH);

// LOCK_EX — Lock exclusivo (escrita). Apenas um processo por vez.
flock($file, LOCK_EX);

// LOCK_UN — Libera o lock.
flock($file, LOCK_UN);

// LOCK_NB — Non-blocking. Retorna na hora se não conseguir o lock.
if (!flock($file, LOCK_EX | LOCK_NB)) {
    echo "Arquivo ocupado no momento.<br>\n
```

> ⚠️ **Cuidado:** `flock()` funciona apenas em sistemas com bloqueio consultivo (advisory locking), como Linux e macOS. No Windows, o comportamento pode variar. `flock()` não funciona com alguns wrappers de stream remotos.

---

## 14. Streams e Wrappers

### `php://input` — Lê o corpo bruto da requisição

```php
<?php
// Útil para APIs que recebem JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['erro' => 'JSON inválido']));
}

echo "Nome recebido: " . ($data['name'] ?? 'não informad
```

### `php://output` — Escreve na saída

```php
<?php
$output = fopen('php://output', 'w');
fputcsv($output, ['Nome', 'Email', 'Idade']);
fputcsv($output, ['João', 'joao@email.com', '28']);
fputcsv($output, ['Maria', 'maria@email.com', '34']);
fclose($output);
// Isso gera CSV na resposta 
```

### `php://memory` e `php://temp` — Arquivos em memória

```php
<?php
// php://memory — armazena tudo em RAM
$memory = fopen('php://memory', 'r+');
fwrite($memory, "Dados temporários em memória\n");
fwrite($memory, "Nada é escrito em disco\n");

rewind($memory);
echo fread($memory, 1024);
fclose($memory);

// php://temp — armazena em RAM até 2MB, depois usa disco
$temp = fopen('php://temp', 'r+');
for ($i = 0; $i < 1000; $i++) {
    fwrite($temp, "Linha {$i}\n");
}
rewind($temp);
echo stream_get_contents($temp);
fclose($te
```

### Wrappers: lendo arquivos de diferentes fontes

```php
<?php
// Arquivos locais
$local = file_get_contents('/caminho/arquivo.txt');

// URLs HTTP (se allow_url_fopen = On)
$remote = file_get_contents('https://jsonplaceholder.typicode.com/todos/1');

// FTP
// $ftp = file_get_contents('ftp://user:password@servidor/arquivo.txt');

// Leitura de stdin (entrada padrão do terminal)
$stdin = file_get_contents('php://stdin');

// Dados enviados via POST tradicional
$post = file_get_contents('php://inpu
```

---

## 15. Exemplo Prático: Sistema de Log

```php
<?php
class Logger {
    private string $file;

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $line = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }

    public function ler(int $lines = 50, string $level = null): array {
        $logs = [];
        $file = fopen($this->file, 'r');
        if ($file === false) {
            return $logs;
        }

        while (($line = fgets($file)) !== false) {
            if ($level !== null && !str_contains($line, $level . ':')) {
                continue;
            }
            $logs[] = rtrim($line);
        }
        fclose($file);

        return array_slice($logs, -$lines);
    }

    public function limpar(): bool {
        return file_put_contents($this->file, '') !== false;
    }
}

// Uso
$logger = new Logger(__DIR__ . '/app.log');
$logger->info('Sistema iniciado', ['versao' => '2.0']);
$logger->error('Falha na conexão com banco', ['erro' => 'timeout']);
print_r($logger->read(10, 'ERROR
```

---

## 16. `request_parse_body()` (PHP 8.4+)

> **PHP 8.4+**

A nova função `request_parse_body()` permite process o corpo da requisição de forma programática, útil para APIs que recebem dados em formatos como JSON.

```php
<?php
// PHP 8.4+ — alternativa a $_POST para APIs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = request_parse_body();

    // Para JSON, combine com php://input
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $json = file_get_contents('php://input');
        $result = json_decode($json, true);
    }

    print_r($result
```

---

## 📚 Referências

- [PHP: Funções de Sistema de Arquivos](https://www.php.net/manual/pt_BR/book.filesystem.php)
- [PHP: fopen](https://www.php.net/manual/pt_BR/function.fopen.php)
- [PHP: manipulando uploads de arquivos](https://www.php.net/manual/pt_BR/features.file-upload.php)
- [PHP: finfo — Fileinfo](https://www.php.net/manual/pt_BR/book.fileinfo.php)
- [PHP: Streams](https://www.php.net/manual/pt_BR/book.stream.php)
- [PHP: flock](https://www.php.net/manual/pt_BR/function.flock.php)
