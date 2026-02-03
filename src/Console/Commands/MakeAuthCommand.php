<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAuthCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:auth')
            ->setDescription('Scaffold basic login and registration views and routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("<info>Scaffolding Authentication...</info>");

        $this->createControllers($output);
        $this->createViews($output);
        $this->appendRoutes($output);

        $output->writeln("\n<info>✨ NanoAuth scaffolded successfully!</info>");
        $output->writeln("<comment>Register and Login routes added to web.php</comment>");
        $output->writeln("<comment>Don't forget to run 'php artisan migrate' if you haven't yet.</comment>");

        return Command::SUCCESS;
    }

    protected function createControllers(OutputInterface $output)
    {
        // src/Core/Console/Commands -> src/Controllers is 4 levels up? No.
        // d:\github\framwork\src\Nano\Framework\Console\Commands
        // d:\github\framwork\src\Controllers
        // Commands (0) -> Console (1) -> Core (2) -> src (3)
        // dirname(__DIR__, 3) is src.

        $srcDir = dirname(__DIR__, 3);
        $base = $srcDir . '/Controllers/Auth';
        if (!is_dir($base)) mkdir($base, 0755, true);

        // LoginController
        $loginStub = <<<EOT
<?php

namespace Nano\Framework\Controllers\Auth;

use Nano\Framework\Facades\Auth;
use Nano\Framework\Facades\View;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class LoginController
{
    public function show()
    {
        return View::render('auth.login');
    }

    public function login(ServerRequestInterface \$request)
    {
        \$data = \$request->getParsedBody();
        
        if (Auth::attempt(['email' => \$data['email'], 'password' => \$data['password']])) {
            return new RedirectResponse('/home');
        }

        return new RedirectResponse('/login');
    }

    public function logout()
    {
        Auth::logout();
        return new RedirectResponse('/login');
    }
}
EOT;
        file_put_contents("{$base}/LoginController.php", $loginStub);

        // RegisterController
        $registerStub = <<<EOT
<?php

namespace Nano\Framework\Controllers\Auth;

use Nano\Framework\Models\User;
use Nano\Framework\Facades\Auth;
use Nano\Framework\Facades\View;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class RegisterController
{
    public function show()
    {
        return View::render('auth.register');
    }

    public function register(ServerRequestInterface \$request)
    {
        \$data = \$request->getParsedBody();
        
        \$user = User::create([
            'name' => \$data['name'],
            'email' => \$data['email'],
            'password' => password_hash(\$data['password'], PASSWORD_DEFAULT)
        ]);

        Auth::login(\$user);

        return new RedirectResponse('/home');
    }
}
EOT;
        file_put_contents("{$base}/RegisterController.php", $registerStub);

        // HomeController
        $homeStub = <<<EOT
<?php

namespace Nano\Framework\Controllers;

use Nano\Framework\Facades\Auth;
use Nano\Framework\Facades\View;

class HomeController
{
    public function index()
    {
        return View::render('home', ['user' => Auth::user()]);
    }
}
EOT;
        file_put_contents($srcDir . '/Controllers/HomeController.php', $homeStub);

        $output->writeln("<info>Controllers created: Login, Register, Home</info>");
    }

    protected function createViews(OutputInterface $output)
    {
        $rootDir = dirname(__DIR__, 4);
        $viewsPath = $rootDir . '/resources/views';
        $authPath = $viewsPath . '/auth';
        $layoutPath = $viewsPath . '/layouts';

        if (!is_dir($authPath)) mkdir($authPath, 0755, true);
        if (!is_dir($layoutPath)) mkdir($layoutPath, 0755, true);

        // Layout View
        $layoutView = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NanoPHP Auth</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; }
        nav { background: #1e293b; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #38bdf8; text-decoration: none; margin-left: 1rem; font-weight: bold; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #1e293b; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .auth-container h2 { color: #38bdf8; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        input { width: 100%; padding: 0.75rem; background: #0f172a; border: 1px solid #334155; border-radius: 4px; color: white; box-sizing: border-box; }
        button { background: #38bdf8; color: #0f172a; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #7dd3fc; }
    </style>
</head>
<body>
    <nav>
        <div class="logo"><a href="/">NanoPHP</a></div>
        <div class="links">
            @if(App\Facades\Auth::check())
                <span>Welcome, {{ App\Facades\Auth::user()->name }}</span>
                <a href="/logout">Logout</a>
            @else
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            @endif
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
EOT;
        file_put_contents("{$layoutPath}/app.nano.php", $layoutView);

        // Login View
        $loginView = <<<EOT
@extends('layouts.app')

@section('content')
<div class="auth-container">
    <h2>Login</h2>
    <form action="/login" method="POST">
        @csrf
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="/register">Register</a></p>
</div>
@endsection
EOT;
        file_put_contents("{$authPath}/login.nano.php", $loginView);

        // Register View
        $registerView = <<<EOT
@extends('layouts.app')

@section('content')
<div class="auth-container">
    <h2>Register</h2>
    <form action="/register" method="POST">
        @csrf
        <div class="form-group">
            <input type="text" name="name" placeholder="Name" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Register</button>
    </form>
</div>
@endsection
EOT;
        file_put_contents("{$authPath}/register.nano.php", $registerView);

        $output->writeln("<info>Views created: login, register, layout</info>");
    }

    protected function appendRoutes(OutputInterface $output)
    {
        $rootDir = dirname(__DIR__, 4);
        $routesPath = $rootDir . '/routes/web.php';

        $content = file_get_contents($routesPath);
        if (str_contains($content, 'Auth Routes')) {
            $output->writeln("<comment>Auth routes already exist in web.php</comment>");
            return;
        }

        $routes = <<<EOT

// Auth Routes
Route::get('/login', [App\Controllers\Auth\LoginController::class, 'show']);
Route::post('/login', [App\Controllers\Auth\LoginController::class, 'login']);
Route::get('/register', [App\Controllers\Auth\RegisterController::class, 'show']);
Route::post('/register', [App\Controllers\Auth\RegisterController::class, 'register']);
Route::get('/logout', [App\Controllers\Auth\LoginController::class, 'logout']);

// Dashboard
Route::get('/home', [App\Controllers\HomeController::class, 'index'])->middleware('auth.middleware');
EOT;
        file_put_contents($routesPath, $routes, FILE_APPEND);
        $output->writeln("<info>Routes appended to web.php</info>");
    }
}

