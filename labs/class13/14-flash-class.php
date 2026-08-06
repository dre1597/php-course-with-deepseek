<?php
class FlashMessages {
    private const KEY = '_flash_messages';

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    public static function set(string $type, string $message): void {
        self::init();
        $_SESSION[self::KEY][$type] = $message;
    }

    public static function get(string $type): ?string {
        self::init();
        $msg = $_SESSION[self::KEY][$type] ?? null;
        unset($_SESSION[self::KEY][$type]);
        return $msg;
    }

    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error', $msg); }
    public static function warning(string $msg): void { self::set('warning', $msg); }
    public static function info(string $msg): void    { self::set('info', $msg); }

    public static function all(): array {
        self::init();
        $messages = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];
        return $messages;
    }

    public static function render(): string {
        $html = '';
        foreach (self::all() as $type => $msg) {
            $html .= sprintf(
                '<div class="flash flash-%s">%s</div>',
                htmlspecialchars($type),
                htmlspecialchars($msg)
            );
        }
        return $html;
    }
}

// Uso
FlashMessages::success('Arquivo enviado!');
FlashMessages::error('Falha na conexão.');
echo FlashMessages::render();
