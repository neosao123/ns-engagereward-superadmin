<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Enables factory support for this model (used in testing/seeding)
    use HasFactory;
    
    // Allow mass assignment for these specific columns
    protected $fillable = [
        'logo_image',        // Stores the logo image path or filename
        'contact_email',     // General contact email address
        'contact_phone',     // General contact phone number
        'support_email',     // Support-specific email address
        'support_contact',   // Support-specific contact phone number
    ];
    
    /**
     * Filters settings based on a search term with optional pagination.
     *
     * @param string $searchTerm Term to search within contact/support fields
     * @param int $limit Number of records to return (0 = all)
     * @param int $skip Number of records to skip (for pagination)
     * @return array Returns total count and filtered result set
     */
    public static function filterData(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        // Start query excluding soft-deleted records
        $query = self::whereNull('deleted_at');

        // Apply search conditions if a search term is provided
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                // Check if search term exists in any of these fields
                $query->where('contact_email', 'like', "%{$searchTerm}%")
                      ->orWhere('contact_phone', 'like', "%{$searchTerm}%")
                      ->orWhere('support_email', 'like', "%{$searchTerm}%")
                      ->orWhere('support_contact', 'like', "%{$searchTerm}%");
            });
        }

        // Count total records matching the query before applying pagination
        $total = $query->count();

        // Apply pagination: limit and skip if a limit is provided
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the final result set, ordered by latest entries first
        $result = $query->orderBy('id', 'DESC')->get();

        // Return both total count and result list
        return [
            "totalRecords" => $total, // Total filtered records (for frontend pagination)
            "result" => $result       // Actual list of Setting model records
        ];
    }
}
