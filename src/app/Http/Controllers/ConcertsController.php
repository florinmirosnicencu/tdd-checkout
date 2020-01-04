<?php

namespace App\Http\Controllers;

use App\Concert;

class ConcertsController extends Controller
{
    public function show($id)
    {
        $concert = Concert::published()->where('id', $id)->first();

        if (is_null($concert)) {
            return response()->json([], 404);
        }

        return view('concerts.show', ['concert' => $concert]);
    }
}
