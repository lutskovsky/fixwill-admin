<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->truncate();

        // Path to the CSV file
        $csvFile = database_path('seeders/statuses.csv');

        // Read the CSV file
        $csvData = File::get($csvFile);
        $rows = array_map('str_getcsv', explode("\n", $csvData));

        // Remove the header row
        array_shift($rows);

        // Process each row
        foreach ($rows as $row) {
            if (count($row) === 4) {
                [$status_id, $status_name, $status_type, $transit] = $row;

                // Map the CSV data to the database fields
                DB::table('statuses')->updateOrInsert(
                    ['status_id' => (int)$status_id,],
                    [
                    'accepted_by_operator' => in_array($status_type, ['valid', 'success']), // True if 'valid' or 'success'
                    'success_for_operator' => $status_type === 'success', // True if 'success'
                    'transit' => (bool)$transit, // Convert to boolean
                ]);
            }
        }
    }
}
