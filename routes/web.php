<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This application uses Next.js as the UI layer. Laravel web routes
| redirect all browser traffic to the Next app, while API routes remain in
| routes/api.php.
|
*/

$nextAppUrl = rtrim((string) env('NEXT_APP_URL', 'http://localhost:3000'), '/');

Route::get('/{any?}', function (Request $request, ?string $any = null) use ($nextAppUrl) {
    $path = trim((string) ($any ?? ''), '/');
    $target = $nextAppUrl . ($path !== '' ? '/' . $path : '');
    $query = $request->getQueryString();

    if ($query) {
        $target .= '?' . $query;
    }

    return redirect()->away($target);
})->where('any', '.*');
