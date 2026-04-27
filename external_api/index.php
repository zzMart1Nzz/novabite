<?php

declare(strict_types=1);

function readEnvFile(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [];
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if ($value !== '' && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        $vars[$key] = $value;
    }

    return $vars;
}

function envVar(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false) {
        return (string) $value;
    }

    if (array_key_exists($key, $_ENV)) {
        return is_string($_ENV[$key]) ? $_ENV[$key] : (string) $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER)) {
        return is_string($_SERVER[$key]) ? $_SERVER[$key] : (string) $_SERVER[$key];
    }

    return null;
}

function firstEnvValue(array $keys): ?string
{
    foreach ($keys as $key) {
        $value = envVar((string) $key);
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }
    }

    return null;
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalizeName(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '';
    }

    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $ascii = is_string($ascii) ? $ascii : $name;
    $ascii = strtolower($ascii);
    $ascii = preg_replace('/[^a-z0-9]+/u', ' ', $ascii) ?? $ascii;
    $ascii = preg_replace('/\s+/u', ' ', $ascii) ?? $ascii;

    return trim($ascii);
}

function resolveImage(string $imagesDir, string $name): ?string
{
    $extensions = ['png', 'jpg', 'jpeg', 'webp'];
    $name = trim($name);
    if ($name === '') {
        return null;
    }

    foreach ($extensions as $ext) {
        $candidate = $name.'.'.$ext;
        if (is_file($imagesDir.DIRECTORY_SEPARATOR.$candidate)) {
            return $candidate;
        }
    }

    $normalized = normalizeName($name);
    if ($normalized === '') {
        return null;
    }

    $underscored = str_replace(' ', '_', $normalized);
    $hyphened = str_replace(' ', '-', $normalized);

    foreach ([$normalized, $underscored, $hyphened] as $base) {
        foreach ($extensions as $ext) {
            $candidate = $base.'.'.$ext;
            if (is_file($imagesDir.DIRECTORY_SEPARATOR.$candidate)) {
                return $candidate;
            }
        }
    }

    return null;
}

function baseUrl(): string
{
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';

    if (is_string($forwardedProto) && trim($forwardedProto) !== '') {
        $scheme = strtolower(trim(explode(',', $forwardedProto)[0]));
    } else {
        $https = $_SERVER['HTTPS'] ?? '';
        $scheme = (! empty($https) && $https !== 'off') ? 'https' : 'http';
    }

    if (is_string($forwardedHost) && trim($forwardedHost) !== '') {
        $host = trim(explode(',', $forwardedHost)[0]);
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    }

    return $scheme.'://'.$host;
}

function scriptDir(): string
{
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = $dir === '.' ? '' : $dir;

    return rtrim($dir, '/');
}

function relativePath(string $path): string
{
    $path = $path !== '' ? $path : '/';
    $dir = scriptDir();

    if ($dir !== '' && str_starts_with($path, $dir)) {
        $path = substr($path, strlen($dir)) ?: '/';
    }

    if (str_starts_with($path, '/index.php')) {
        $path = substr($path, strlen('/index.php')) ?: '/';
    }

    return $path !== '' ? $path : '/';
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$relPath = relativePath((string) $path);

if (str_starts_with($relPath, '/images/')) {
    $filename = basename(substr($relPath, strlen('/images/')));
    if ($filename === '' || $filename === '.' || $filename === '..') {
        http_response_code(404);
        exit;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
        http_response_code(404);
        exit;
    }

    $filePath = __DIR__.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$filename;
    if (! is_file($filePath)) {
        http_response_code(404);
        exit;
    }

    $mime = match ($ext) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };

    header('Content-Type: '.$mime);
    header('Cache-Control: public, max-age=86400');
    readfile($filePath);
    exit;
}

if (! in_array($relPath, ['/', '/produktuak'], true)) {
    jsonResponse(['error' => 'Not Found'], 404);
}

$fileEnv = array_merge(
    readEnvFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'.env'),
    readEnvFile(__DIR__.DIRECTORY_SEPARATOR.'.env'),
);

$host = firstEnvValue(['NOVABITES_API_DB_HOST', 'DB_HOST']) ?? ($fileEnv['DB_HOST'] ?? '127.0.0.1');
$port = firstEnvValue(['NOVABITES_API_DB_PORT', 'DB_PORT']) ?? ($fileEnv['DB_PORT'] ?? '3306');
$database = firstEnvValue(['NOVABITES_API_DB_DATABASE', 'DB_DATABASE']) ?? ($fileEnv['DB_DATABASE'] ?? '');
$username = firstEnvValue(['NOVABITES_API_DB_USERNAME', 'DB_USERNAME']) ?? ($fileEnv['DB_USERNAME'] ?? '');
$password = envVar('NOVABITES_API_DB_PASSWORD') ?? envVar('DB_PASSWORD') ?? ($fileEnv['DB_PASSWORD'] ?? '');

if ($database === '' || $username === '') {
    jsonResponse([
        'error' => 'DB config missing (DB_DATABASE/DB_USERNAME). Provide environment variables (recommended) or a .env file.',
        'tablasDisponibles' => false,
        'motas' => [],
    ], 500);
}

try {
    $dsn = 'mysql:host='.$host.';port='.$port.';dbname='.$database.';charset=utf8mb4';
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'error' => 'DB connection failed.',
        'tablasDisponibles' => false,
        'motas' => [],
    ], 500);
}

try {
    $stmt = $pdo->query('SELECT id, izena, prezioa, mota, stock FROM produktuak ORDER BY mota ASC, izena ASC');
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    jsonResponse([
        'error' => 'Query failed.',
        'tablasDisponibles' => false,
        'motas' => [],
    ], 500);
}

$imagesDir = __DIR__.DIRECTORY_SEPARATOR.'images';
$baseUrl = baseUrl().scriptDir();

$motaLabelMap = [
    'Entranteak' => 'Entranteak',
    'Hamburguesa' => 'Hamburguesak',
    'Entsalada' => 'Entsaladak',
    'Pizza' => 'Pizzak',
    'Sandwich' => 'Sandwichak',
    'Haragia' => 'Haragiak',
    'Edaria' => 'Edariak',
    'Besteak' => 'Besteak',
];

$motaOrder = [
    'Entranteak',
    'Hamburguesa',
    'Entsalada',
    'Pizza',
    'Sandwich',
    'Haragia',
    'Edaria',
    'Besteak',
];

$grouped = [];
foreach ($rows as $row) {
    $mota = trim((string) ($row['mota'] ?? ''));

    if ($mota === 'Hamburgesa') {
        $mota = 'Hamburguesa';
    }

    $motaKey = $mota !== '' ? $mota : 'Besteak';

    if (! array_key_exists($motaKey, $grouped)) {
        $grouped[$motaKey] = [];
    }

    $name = (string) ($row['izena'] ?? '');
    $image = is_dir($imagesDir) ? resolveImage($imagesDir, $name) : null;

    $grouped[$motaKey][] = [
        'id' => $row['id'] ?? null,
        'izena' => $name,
        'prezioa' => $row['prezioa'] ?? null,
        'stock' => $row['stock'] ?? null,
        'mota' => $mota,
        'irudia' => $image,
        'irudia_url' => $image ? $baseUrl.'/images/'.rawurlencode($image) : null,
    ];
}

uksort($grouped, function ($a, $b) use ($motaOrder) {
    $posA = array_search((string) $a, $motaOrder, true);
    $posB = array_search((string) $b, $motaOrder, true);

    $posA = $posA === false ? 999 : $posA;
    $posB = $posB === false ? 999 : $posB;

    if ($posA !== $posB) {
        return $posA <=> $posB;
    }

    return strcmp(strtolower((string) $a), strtolower((string) $b));
});

$motas = [];
foreach ($grouped as $motaName => $produktuak) {
    $motas[] = [
        'mota' => $motaLabelMap[$motaName] ?? $motaName,
        'produktuak' => $produktuak,
    ];
}

jsonResponse([
    'tablasDisponibles' => true,
    'motas' => $motas,
]);
