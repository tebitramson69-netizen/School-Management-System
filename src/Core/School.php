<?php

declare(strict_types=1);

require_once __DIR__ . '/../Models/SchoolSettings.php';

class School
{
    private static ?array $settings = null;

    public static function settings(): array
    {
        if (self::$settings === null) {
            $model = new SchoolSettings();
            self::$settings = $model->get();
        }

        return self::$settings;
    }
}