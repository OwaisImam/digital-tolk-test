<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index()
    {
        $result = [];
    
        Translation::select('locale', 'group', 'key', 'value')
            ->cursor()
            ->each(function($item) use (&$result) {
                $result[$item->locale][$item->group ?? 'default'][$item->key] = $item->value;
            });
    
        return response()->json($result);
    }
}
