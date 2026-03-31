<?php

use App\Actions\Recommendations\BuildGameRecommendations;
use App\Mail\WeeklyRecommendationsMail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rawg:sync-games {--pages=5} {--page-size=20} {--start-page=1} {--ordering=-rating}', function () {
    $pages = (int) $this->option('pages');
    $pageSize = (int) $this->option('page-size');
    $startPage = (int) $this->option('start-page');
    $ordering = (string) $this->option('ordering');

    $this->info("Syncing RAWG games from page {$startPage} for {$pages} page(s) at {$pageSize} items per page.");

    $stats = app(\App\Services\RawgImportService::class)->importGames(
        pages: $pages,
        pageSize: $pageSize,
        startPage: $startPage,
        ordering: $ordering,
        progress: fn (string $message) => $this->line($message)
    );

    $this->newLine();
    $this->table(
        ['Processed', 'Created', 'Updated', 'Skipped'],
        [[
            $stats['processed'],
            $stats['created'],
            $stats['updated'],
            $stats['skipped'],
        ]]
    );
})->purpose('Import games from RAWG into the local database');

Artisan::command('recommendations:send-weekly {--user-id=} {--force}', function () {
    $userId = $this->option('user-id');
    $force = (bool) $this->option('force');

    $query = User::query()
        ->whereNotNull('email_verified_at');

    if (!$force) {
        $query->where('weekly_recommendation_emails', true);
    }

    if ($userId) {
        $query->where('id', (int) $userId);
    }

    $users = $query->get();

    if ($users->isEmpty()) {
        $this->info('No users matched the weekly recommendation email criteria.');
        return;
    }

    $action = app(BuildGameRecommendations::class);
    $sent = 0;

    foreach ($users as $user) {
        $recommendations = $action->handle($user);
        $unsubscribeUrl = URL::temporarySignedRoute(
            'recommendations.unsubscribe',
            now()->addDays(30),
            ['user' => $user->id]
        );

        Mail::to($user->email)->send(
            new WeeklyRecommendationsMail($user, $recommendations, $unsubscribeUrl)
        );

        $sent++;
        $this->line("Sent weekly recommendations to {$user->email}");
    }

    $this->info("Weekly recommendation emails sent: {$sent}");
})->purpose('Send weekly recommendation emails to subscribed users');

Schedule::command('recommendations:send-weekly')->weeklyOn(1, '09:00');
