<?php

namespace App\Http\Controllers;

use App\Services\UpdateService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    protected $updateService;

    public function __construct(UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    public function index()
    {
        $status = $this->updateService->check();
        return view('settings.update', compact('status'));
    }

    public function update(Request $request)
    {
        $result = $this->updateService->update();
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        
        return redirect()->back()->with('error', 'Update Failed: ' . $result['message']);
    }
}
