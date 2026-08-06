<?php
function uploadSeguro(array $file, string $dir, array $options = []): array {
    $options = array_merge([
        'max_size'   => 5 * 1024 * 1024,
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'rename'     => true,
    ], $options);

    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload: código ' . $file['error'];
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > $options['max_size']) {
        $errors[] = 'Arquivo maior que o permitido (' . ($options['max_size'] / 1024 / 1024) . ' MB).';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($realMime, $options['mime_types'])) {
        $errors[] = "Tipo de arquivo '{$realMime}' não permitido.";
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $options['extensions'])) {
        $errors[] = "Extensão '{$extension}' não permitida.";
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if ($options['rename']) {
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    } else {
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $fileName = $safeName;
    }

    $destination = rtrim($dir, '/') . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success'       => true,
            'original_name' => $file['name'],
            'final_name'    => $fileName,
            'path'          => $destination,
            'size'          => $file['size'],
            'type'          => $realMime,
        ];
    }

    return ['success' => false, 'errors' => ['Falha ao mover o arquivo.']];
}
