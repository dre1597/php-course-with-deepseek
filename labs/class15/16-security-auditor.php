<?php
// checklist-seguranca.php — Rode em desenvolvimento para auditar sua aplicação

class SecurityAuditor {
    private array $warnings = [];

    public function audit(): array {
        // PHP Version
        if (version_compare(PHP_VERSION, '8.2', '<')) {
            $this->warnings[] = "PHP " . PHP_VERSION . " está desatualizado. Use 8.2+.";
        }

        // expose_php
        if (ini_get('expose_php')) {
            $this->warnings[] = "'expose_php' está ligado. Desabilite em produção.";
        }

        // display_errors
        if (ini_get('display_errors')) {
            $this->warnings[] = "'display_errors' está ligado. Desabilite em produção.";
        }

        // Session security
        if (!ini_get('session.cookie_httponly')) {
            $this->warnings[] = "'session.cookie_httponly' desabilitado.";
        }

        if (!ini_get('session.use_strict_mode')) {
            $this->warnings[] = "'session.use_strict_mode' desabilitado.";
        }

        // allow_url_include
        if (ini_get('allow_url_include')) {
            $this->warnings[] = "'allow_url_include' está ligado. DESABILITE IMEDIATAMENTE!";
        }

        return $this->warnings;
    }

    public function getWarnings(): array {
        return $this->warnings;
    }
}

$auditor = new SecurityAuditor();
$results = $auditor->audit();

if (empty($results)) {
    echo "Nenhum problema crítico encontrado.<br>\n";
} else {
    echo "<h3>Problemas de segurança detectados:</h3>\n<ul>\n";
    foreach ($results as $warning) {
        echo "<li>" . h($warning) . "</li>\n";
    }
    echo "</ul>\n";
}
