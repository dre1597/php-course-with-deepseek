<?php

function monitorDirectory(string $dir, string $snapshotFile): array
{
    if (!is_dir($dir)) {
        return emptyDiff();
    }

    $previous     = loadSnapshot($snapshotFile);
    $snapshotReal = realpath($snapshotFile) ?: $snapshotFile;
    $current      = scanDirectory($dir, $snapshotReal);
    $result       = diffSnapshots($current, $previous);

    file_put_contents($snapshotFile, json_encode($current, JSON_PRETTY_PRINT), LOCK_EX);

    sort($result['new']);
    sort($result['modified']);
    sort($result['removed']);

    return $result;
}

function emptyDiff(): array
{
    return ['new' => [], 'modified' => [], 'removed' => []];
}

function loadSnapshot(string $snapshotFile): array
{
    if (!file_exists($snapshotFile)) {
        return [];
    }

    return json_decode(file_get_contents($snapshotFile), true) ?: [];
}

function scanDirectory(string $dir, string $snapshotReal): array
{
    $current = [];

    foreach (scandir($dir) as $entry) {
        $path = $dir . '/' . $entry;

        if (!is_file($path)) {
            continue;
        }

        $pathReal = realpath($path) ?: $path;
        if ($pathReal === $snapshotReal) {
            continue;
        }

        $current[$entry] = filemtime($path);
    }

    return $current;
}

function diffSnapshots(array $current, array $previous): array
{
    $result = emptyDiff();

    foreach ($current as $name => $mtime) {
        if (!array_key_exists($name, $previous)) {
            $result['new'][] = $name;
        } elseif ($previous[$name] !== $mtime) {
            $result['modified'][] = $name;
        }
    }

    foreach ($previous as $name => $mtime) {
        if (!array_key_exists($name, $current)) {
            $result['removed'][] = $name;
        }
    }

    return $result;
}
