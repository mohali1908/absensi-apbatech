<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;

class AbsenController extends Controller
{
    public function show()
    {
        $id = request('id');
        return Presence::findOrFail($id);
    }
}
