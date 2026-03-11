<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('role', 'Администратор')->first();
$token = $user->createToken('test')->plainTextToken;

echo "Token: $token\n\n";

// Clear
$client = new GuzzleHttp\Client;
$response = $client->post('http://localhost:8000/api/import/clear', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
echo 'Clear: '.$response->getStatusCode()."\n";

// Import
$response = $client->post('http://localhost:8000/api/import/run', [
    'headers' => ['Authorization' => 'Bearer '.$token],
    'form_params' => ['aircraft_path' => 'КЛЕН', 'force' => true],
    'timeout' => 180,
]);

$data = json_decode($response->getBody(), true);
echo 'Import success: '.($data['success'] ?? 'false')."\n";
echo 'Courses: '.($data['result']['courses_created'] ?? 0)."\n";
echo 'Questions: '.($data['result']['questions_imported'] ?? 0)."\n";
echo 'Answers: '.($data['result']['answers_imported'] ?? 0)."\n";

if (! empty($data['result']['errors'])) {
    echo "Errors:\n";
    print_r($data['result']['errors']);
}
