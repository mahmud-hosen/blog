<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;


class PersonController extends Controller
{

    public function store(Request $request)
    {
        //dd('Person information submitted successfully!', $request);

        $request->validate([
            'name' => 'required|string|max:5',
            'age' => 'required|integer|min:0',
        ]);

        // Save data using the Person model
        $person = new Person();
        $person->name = $request['name'];
        $person->age = $request['age'];
        $person->save();

        dd('Person information submitted successfully!');


        return redirect()->back()->with('success', 'Person information submitted successfully.');
    }
}
