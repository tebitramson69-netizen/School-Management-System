<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class SchoolSettings
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get the school's current configuration.
     */
    public function get(): array
    {
        $sql = "
            SELECT
                id,
                school_name,
                motto,
                logo_path,
                address,
                phone,
                email,
                website,
                school_type,
                primary_color,
                secondary_color,
                pass_mark,
                created_at,
                updated_at
            FROM school_settings
            ORDER BY id ASC
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);

        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        /*
         * Safety fallback.
         *
         * The application should still function even if
         * the configuration record has not been created.
         */
        if (!$settings) {
            return [
                'id' => null,
                'school_name' => 'School Management System',
                'motto' => null,
                'logo_path' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'school_type' => null,
                'primary_color' => '#123B63',
                'secondary_color' => '#D69E2E',
                'pass_mark' => '10.00',
                'created_at' => null,
                'updated_at' => null
            ];
        }

        return $settings;
    }
}