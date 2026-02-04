<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we use array drivers for testing to avoid side effects and database issues
        config(['session.driver' => 'array']);
        config(['cache.default' => 'array']);
    }

    /**
     * Format time string based on database driver.
     * SQLite often returns H:i while PostgreSQL/MySQL return H:i:s
     */
    protected function formatTime(string $time): string
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return $driver === 'sqlite' ? date('H:i', strtotime($time)) : date('H:i:s', strtotime($time));
    }

    /**
     * Format date string based on database driver.
     * SQLite often returns Y-m-d 00:00:00 for date columns
     */
    protected function formatDate(string $date): string
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return $driver === 'sqlite' ? Carbon::parse($date)->format('Y-m-d 00:00:00') : $date;
    }
}
