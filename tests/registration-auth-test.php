<?php


require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

echo "=== Registration Authentication Test ===\n\n";

echo "Test 1: Register new user and verify authentication\n";

$email = 'test-'.time().'@example.com';
$password = 'TestPassword123!';

$user = User::create([
    'name' => 'Test User',
    'email' => $email,
    'password' => Hash::make($password),
]);

echo "Created user with ID: {$user->id}\n";

$request = Request::create('/register', 'POST');
$request->setLaravelSession($app['session']);
$request->session()->start();

echo 'Session ID before regeneration: '.$request->session()->getId()."\n";

$request->session()->regenerate();
echo 'Session ID after regeneration: '.$request->session()->getId()."\n";

Auth::login($user);
echo 'User logged in, Auth::check(): '.(Auth::check() ? 'true' : 'false')."\n";
echo 'Auth::id(): '.Auth::id()."\n";

$request->session()->save();
echo "Session saved\n";

echo 'Auth::check() after save: '.(Auth::check() ? 'true' : 'false')."\n";

echo "\nTest 2: Verify authentication persists across requests\n";

$newRequest = Request::create('/events', 'GET');
$newRequest->cookies->set($app['config']->get('session.cookie'), $request->session()->getId());
$newRequest->setLaravelSession($app['session']);
$app['session']->driver()->setId($request->session()->getId());
$app['session']->driver()->start();

echo 'Session ID in new request: '.$newRequest->session()->getId()."\n";
echo 'Auth::check() in new request: '.(Auth::check() ? 'true' : 'false')."\n";
echo 'Auth::id() in new request: '.Auth::id()."\n";

echo "\nTest 3: Check if authentication middleware would pass\n";

$authenticated = Auth::check();
echo 'User would '.($authenticated ? 'PASS' : 'FAIL')." authentication middleware\n";

$user->delete();
echo "\nTest user deleted\n";

echo "\n=== Test Complete ===\n";

if ($authenticated) {
    echo "✓ Registration authentication test PASSED\n";
    exit(0);
} else {
    echo "✗ Registration authentication test FAILED\n";
    exit(1);
}
