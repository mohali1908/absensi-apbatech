<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function show()
    {
        $id = request('id');
        return Leave::findOrFail($id);
    }
}
