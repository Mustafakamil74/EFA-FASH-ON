<?php

namespace App\Core;

/**
 * Simple PHP-template view renderer with layout support.
 * Views live in app/Views and use plain PHP with the e() helper for escaping.
 */
class View
{
    /**
     * Render a view inside a layout.
     *
     * @param string $view   dot path e.g. 'customers.index'
     * @param array  $data   variables exposed to the view
     * @param string|null $layout layout name in app/Views/layouts, or null for none
     */
    public static function render(string $view, array $data = [], ?string $layout = 'app'): string
    {
        $content = self::partial($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::partial('layouts.' . $layout, array_merge($data, ['content' => $content]));
    }

    /** Render a view fragment without a layout and return the HTML. */
    public static function partial(string $view, array $data = []): string
    {
        $path = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$view} ({$path})");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
