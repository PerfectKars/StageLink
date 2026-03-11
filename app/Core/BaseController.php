<?php
declare(strict_types=1);
namespace App\Core;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = ROOT_PATH . '/app/Views/' . $view . '.php';
        if (!file_exists($viewFile)) throw new \RuntimeException("Vue introuvable : $viewFile");
        require ROOT_PATH . '/templates/header.php';
        require $viewFile;
        require ROOT_PATH . '/templates/footer.php';
    }

    protected function renderPartial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require ROOT_PATH . '/app/Views/' . $view . '.php';
    }

    protected function redirect(string $url): void { header('Location: ' . $url); exit; }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user'])) $this->redirect('/login');
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
            http_response_code(403);
            $this->render('error/403');
            exit;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
