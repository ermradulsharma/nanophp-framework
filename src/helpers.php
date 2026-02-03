<?php

use Nano\Framework\View;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

if (!function_exists('view')) {
    function view(string $view, array $data = []): HtmlResponse
    {
        return new HtmlResponse(\Nano\Framework\Facades\View::render($view, $data));
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return \Nano\Framework\Router::getUrl($name, $params);
    }
}

if (!function_exists('json')) {
    function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('vite')) {
    function vite(string $asset): string
    {
        $devServer = "http://localhost:5173";
        $manifestPath = base_path('public/build/.vite/manifest.json');

        if (!file_exists($manifestPath)) {
            $manifestPath = base_path('public/build/manifest.json');
        }

        // Try to check if dev server is running (check both IPv4 and IPv6)
        $isDev = false;
        $hosts = ["127.0.0.1", "[::1]", "localhost"];
        foreach ($hosts as $host) {
            $fp = @fsockopen($host, 5173, $errno, $errstr, 0.05);
            if ($fp) {
                $isDev = true;
                fclose($fp);
                break;
            }
        }

        if ($isDev) {
            $html = "<script type=\"module\" src=\"{$devServer}/@vite/client\"></script>";
            $assetPath = "{$devServer}/resources/" . $asset;

            if (str_ends_with($asset, '.css')) {
                return $html . "<script type=\"module\" src=\"{$assetPath}\"></script>";
            }

            if (str_ends_with($asset, '.jsx') || str_ends_with($asset, '.tsx')) {
                $script = "<script type=\"module\">\n";
                $script .= "import RefreshRuntime from '{$devServer}/@react-refresh';\n";
                $script .= "RefreshRuntime.injectIntoGlobalHook(window);\n";
                $script .= "window.\$RefreshReg$ = () => {};\n";
                $script .= "window.\$RefreshSig$ = () => (type) => type;\n";
                $script .= "window.__vite_plugin_react_preamble_installed__ = true;\n";
                $script .= "</script>\n";
                return $script . "<script type=\"module\" src=\"{$assetPath}\"></script>";
            }

            return "<script type=\"module\" src=\"{$assetPath}\"></script>";
        }

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $key = "resources/" . $asset;
            if (isset($manifest[$key])) {
                $file = $manifest[$key]['file'];
                if (str_ends_with($asset, '.css')) {
                    return "<link rel=\"stylesheet\" href=\"/build/{$file}\">";
                } else {
                    return "<script type=\"module\" src=\"/build/{$file}\"></script>";
                }
            }
        }

        return "";
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed|\DI\Container
     */
    function app(?string $abstract = null, array $parameters = [])
    {
        static $container = null;

        if ($container === null) {
            // Get container from bootstrap
            $app = require __DIR__ . '/../../../bootstrap/app.php';
            $container = $app->getContainer();
        }

        if (is_null($abstract)) {
            return $container;
        }

        return $container->get($abstract);
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $_ENV;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_ENV[$k] = $v;
            }
            return null;
        }

        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return __DIR__ . '/../../../storage' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/../../../' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     * @return string
     */
    function class_basename($class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
