<?php

namespace App\Services;

class GstService
{
    /**
     * Validate GSTIN format
     */
    public function validateFormat($gst)
    {
        // 15 characters alphanumeric
        // Format: 2 digits state code, 10 chars PAN, 1 char entity number, 1 char Z, 1 char check digit
        $regex = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        return preg_match($regex, $gst) === 1;
    }

    /**
     * Fetch GST details (simulated)
     */
    public function getDetails($gst)
    {
        // In a real application, this would call an external API
        // For now, we simulate a successful response
        
        // Return simulated data
        return [
            'name' => 'Demo Enterprise',
            'address' => 'Plot No. 123, Industrial Area, Phase 1, City - 400001, State',
        ];
    }
}
