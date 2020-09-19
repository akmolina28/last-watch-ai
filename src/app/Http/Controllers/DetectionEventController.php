<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JavaScript;

class DetectionEventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view ("events.index", [
            'events' => DetectionEvent::withCount(['detectionProfiles'])->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//    public function store(Request $request)
//    {
//        //
//    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DetectionEvent  $detectionEvent
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(DetectionEvent $event)
    {
        $event->load(['aiPredictions.detectionProfiles']);

        JavaScript::put([
            'file_name' => basename($event->image_file_name),
            'predictions' => $event->aiPredictions
        ]);

        return view('events.show', [
            'event' => $event
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DetectionEvent  $detectionEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(DetectionEvent $detectionEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DetectionEvent  $detectionEvent
     * @return \Illuminate\Http\Response
     */
//    public function update(Request $request, DetectionEvent $detectionEvent)
//    {
//        //
//    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DetectionEvent  $detectionEvent
     * @return \Illuminate\Http\Response
     */
//    public function destroy(DetectionEvent $detectionEvent)
//    {
//        //
//    }
}
