<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Get the currently selected company ID from session.
     */
    protected function getCompanyId(): ?int
    {
        return session('selected_company_id');
    }
}
