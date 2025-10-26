<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function clubsCreate()
    {
        try {
            $fields = Field::all();
            $users = User::where('is_admin', false)->get();
            
            return response()->json([
                'success' => true,
                'fields_count' => $fields->count(),
                'users_count' => $users->count(),
                'fields' => $fields->toArray(),
                'users' => $users->toArray(),
                'message' => 'Data loaded successfully from TestController'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
}












