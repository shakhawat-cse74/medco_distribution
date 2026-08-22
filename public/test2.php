<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// bypass CSRF for test
$app->bind(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, function () {
    return new class {
        public function handle($request, $next) { return $next($request); }
    };
});
$request = Illuminate\Http\Request::create('/category', 'POST', [
    'name' => 'Test Cat Request 2',
    'parent_id' => '1',
    'is_active' => '1',
    'ajax' => '0'
]);
$response = $kernel->handle($request);
if ($response->isRedirect()) {
    $errors = session('errors');
    if ($errors) {
        echo "Validation Errors: \n";
        print_r($errors->all());
    } else {
        echo "Redirected without errors! Location: " . $response->headers->get('Location');
    }
} else {
    echo "Status: " . $response->getStatusCode() . "\n";
}
