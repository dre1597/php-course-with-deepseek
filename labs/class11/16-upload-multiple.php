<?php
// Form: <input type="file" name="photos[]" multiple>

function uploadMultiple(array $photos, string $targetDir, array $allowedTypes, int $maxFileSize): array {
    $uploadedFiles = [];

    for ($i = 0; $i < count($photos['name']); $i++) {
        if ($photos['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // skip files with errors
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
        $fileName = uniqid('photo_') . '_' . time() . '.' . $extension;
        $destination = $targetDir . '/' . $fileName;

        if (move_uploaded_file($photos['tmp_name'][$i], $destination)) {
            $uploadedFiles[] = [
                'original_name' => $photos['name'][$i],
                'final_name'    => $fileName,
                'type'          => $realType,
                'size'          => $photos['size'][$i],
            ];
        }
    }

    return $uploadedFiles;
}
