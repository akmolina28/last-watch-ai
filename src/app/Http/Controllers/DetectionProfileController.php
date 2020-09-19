<?php

namespace App\Http\Controllers;

use App\DetectionProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DetectionProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view("profiles.index", [
            'profiles' => DetectionProfile::get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view("profiles.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'file_pattern' => 'required',
            'min_confidence' => 'required',
            'object_classes' => 'required'
        ]);

        $profile = DetectionProfile::make([
            'name' => $request->get('name'),
            'file_pattern' => $request->get('file_pattern'),
            'min_confidence' => $request->get('min_confidence'),
            'use_regex' => $request->get('use_regex') == 'on',
            'object_classes' => $request->get('object_classes')
        ]);

        $file = $request->file('mask');
        if ($file) {
            $file->storeAs('masks', $profile->slug.'.png', 'public');
            $profile->use_mask = true;
        }
        else {
            $profile->use_mask = false;
        }

        $profile->save();

        return redirect("/profiles/create");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DetectionProfile  $detectionProfile
     * @return \Illuminate\Http\Response
     */
//    public function show(DetectionProfile $detectionProfile)
//    {
//        //
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DetectionProfile  $detectionProfile
     * @return \Illuminate\Http\Response
     */
//    public function edit(DetectionProfile $detectionProfile)
//    {
//        //
//    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DetectionProfile  $detectionProfile
     * @return \Illuminate\Http\Response
     */
//    public function update(Request $request, DetectionProfile $detectionProfile)
//    {
//        //
//    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DetectionProfile  $detectionProfile
     * @return \Illuminate\Http\Response
     */
//    public function destroy(DetectionProfile $detectionProfile)
//    {
//        //
//    }
}
