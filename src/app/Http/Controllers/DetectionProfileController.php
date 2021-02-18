<?php

namespace App\Http\Controllers;

use App\DetectionProfile;
use App\Jobs\EnableDetectionProfileJob;
use App\Resources\DetectionProfileResource;
use App\Resources\ProfileAutomationConfigResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetectionProfileController extends Controller
{
    public function index()
    {
        return DetectionProfileResource::collection(DetectionProfile::orderBy('created_at')->get());
    }

    protected function lookUpProfile($param): DetectionProfile
    {
        $profile = DetectionProfile::where('id', $param)
                    ->orWhere('slug', $param)
                    ->firstOrFail();

        return $profile;
    }

    protected function validateRequest()
    {
        $id = 'id';
        if (request()->has('id')) {
            $id = request()->get('id');
        }

        request()->validate([
            'name' => "required|unique:detection_profiles,name,{$id},id,deleted_at,NULL",
            'file_pattern' => 'required',
            'min_confidence' => 'required|numeric|between:0,1',
            'object_classes' => 'required',
            'smart_filter_precision' => 'numeric|between:0,1',
        ]);
    }

    protected function saveMask($makeName)
    {
        $file = request()->file('mask');

        if ($file) {
            $file->storeAs('masks', $makeName.'.png', 'public');

            return true;
        }

        return false;
    }

    public function make(Request $request)
    {
        $this->validateRequest();

        $profile = DetectionProfile::make([
            'name' => $request->get('name'),
            'file_pattern' => $request->get('file_pattern'),
            'min_confidence' => $request->get('min_confidence'),
            'use_regex' => $request->get('use_regex', 'false') === 'true',
            'is_negative' => $request->get('is_negative', 'false') === 'true',
            'object_classes' => json_decode($request->get('object_classes')),
            'use_smart_filter' => $request->get('use_smart_filter', 'false') === 'true',
            'smart_filter_precision' => $request->get('use_smart_filter', 'false') === 'true' ?
                $request->get('smart_filter_precision') : 0,
        ]);

        $profile->use_mask = $this->saveMask($profile->slug);

        $profile->save();

        return DetectionProfileResource::make($profile);
    }

    public function update($param)
    {
        $profile = $this->lookUpProfile($param);

        $this->validateRequest();

        $profile->name = request()->get('name');
        $profile->file_pattern = request()->get('file_pattern');
        $profile->min_confidence = request()->get('min_confidence');
        $profile->use_regex = request()->get('use_regex', 'false') === 'true';
        $profile->object_classes = json_decode(request()->get('object_classes'));
        $profile->use_smart_filter = request()->get('use_smart_filter', 'false') === 'true';
        $profile->smart_filter_precision = request()->get('use_smart_filter', 'false') === 'true' ?
            request()->get('smart_filter_precision') : 0;
        $profile->is_negative = request()->get('is_negative', 'false') === 'true';
        $profile->use_mask = request()->get('use_mask', 'false') === 'true';

        if ($profile->use_mask) {
            $this->saveMask($profile->slug);
        }

        $profile->save();

        return DetectionProfileResource::make($profile);
    }

    public function show($param)
    {
        $profile = $this->lookUpProfile($param);

        return DetectionProfileResource::make($profile);
    }

    public function edit($param)
    {
        $profile = $this->lookUpProfile($param);

        return DetectionProfileResource::make($profile);
    }

    public function destroy($param)
    {
        $profile = $this->lookUpProfile($param);

        $profile->delete();

        return response()->json(['message' => 'OK'], 200);
    }

    public function updateStatus($param)
    {
        $profile = $this->lookUpProfile($param);

        if (request()->has('status')) {
            $status = request()->get('status');

            if ($status === 'enabled') {
                $profile->is_enabled = true;
            } elseif ($status === 'disabled') {
                $profile->is_enabled = false;

                if (request()->has('period')) {
                    $minutes = request()->get('period');
                    EnableDetectionProfileJob::dispatch($profile)->delay(now()->addMinutes($minutes));
                }
            } elseif ($status === 'as_scheduled') {
                if (request()->has('start_time')) {
                    $profile->start_time = request()->get('start_time');
                }

                if (request()->has('end_time')) {
                    $profile->end_time = request()->get('end_time');
                }

                $profile->is_scheduled = true;
                $profile->is_enabled = true;
            } else {
                return response()
                    ->json(['message' => 'Invalid status "'.$status.'"'], 422);
            }

            $profile->save();

            return response()->json(['message' => 'OK'], 204);
        }

        return response()
            ->json(['message' => 'Missing status key.'], 422);
    }

    public function showAutomations($param)
    {
        // todo: use class name (e.g. App\WebRequestConfig instead of morphs)

        $profile = $this->lookUpProfile($param);

        $configTypes = [];

        $morphs = Relation::morphMap();
        foreach ($morphs as $alias => $type) {
            if (strpos($type, 'Config')) {
                array_push($configTypes, $alias);
            }
        }

        $union = false;
        $query = null;

        foreach ($configTypes as $type) {
            $q = Relation::$morphMap[$type]
            ::leftJoin('automation_configs as ac', function ($join) use ($type, $profile) {
                $join->on('ac.automation_config_id', '=', $type.'.id');
                $join->where('ac.automation_config_type', '=', $type);
                $join->whereNull('ac.deleted_at');
                $join->where('ac.detection_profile_id', '=', $profile->id);
            })
                ->select(
                    $type.'.id as id',
                    DB::raw("'".$type."' as type"),
                    'ac.detection_profile_id as detection_profile_id',
                    'name',
                    'ac.is_high_priority'
                );

            if ($union) {
                $query = $query->unionAll($q);
            } else {
                $query = $q;
                $union = true;
            }
        }

        return ProfileAutomationConfigResource::collection($query->get());
    }

    public function updateAutomations($param)
    {
        $profile = $this->lookUpProfile($param);

        $type = request()->get('type');
        $id = request()->get('id');
        $value = request()->get('value');
        $isHighPriority = request()->get('is_high_priority', false);

        // todo: pass in class name directly so morphmap is not needed
        $automationType = Relation::morphMap()[$type];

        if ($value == 'true') {
            $profile->subscribeAutomation($automationType, $id, $isHighPriority);
        } else {
            $profile->unsubscribeAutomation($automationType, $id);
        }

        return true;
    }
}
