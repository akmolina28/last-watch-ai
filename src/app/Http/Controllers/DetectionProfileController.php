<?php

namespace App\Http\Controllers;

use App\DetectionProfile;
use App\Resources\AutomationConfigResource;
use App\Resources\DetectionProfileResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetectionProfileController extends Controller
{
    public function index()
    {
        return DetectionProfileResource::collection(DetectionProfile::paginate(10));
    }

    public function make(Request $request) {
        $request->validate([
            'name' => 'required|unique:detection_profiles,name,NULL,id,deleted_at,NULL',
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

        return DetectionProfileResource::make($profile);
    }

    public function show(DetectionProfile $profile) {
        return DetectionProfileResource::make($profile);
    }

    public function destroy(DetectionProfile $profile) {
        $profile->delete();
        return response()->json(['message' => 'OK'], 200);
    }

    public function updateStatus(DetectionProfile $profile) {

        if (request()->has('status')) {

            $status = request()->get('status');

            if ($status === 'enabled') {
                $profile->is_enabled = true;
            }

            else if ($status === 'disabled') {
                $profile->is_enabled = false;
            }

            else if ($status === 'as_scheduled') {

                if (request()->has('start_time')) {
                    $profile->start_time = request()->get('start_time');
                }

                if (request()->has('end_time')) {
                    $profile->end_time = request()->get('end_time');
                }

                $profile->is_scheduled = true;
                $profile->is_enabled = true;
            }

            else {
                return response()
                    ->json(['message' => 'Invalid status "'.$status.'"'], 422);
            }

            $profile->save();
            return response()->json(['message' => 'OK'], 204);
        }

        return response()
            ->json(['message' => 'Missing status key.'], 422);
    }

    public function showAutomations(DetectionProfile $profile) {
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
            $q = Relation::$morphMap[$type]//::select('id', 'name', DB::raw("'".$type."' as type"));
            ::leftJoin('automation_configs as ac', function($join) use ($type, $profile) {
                $join->on('ac.automation_config_id', '=', $type.'.id');
                $join->where('ac.automation_config_type', '=', $type);
                $join->where('ac.detection_profile_id', '=', $profile->id);
            })
                ->select($type.'.id as id', DB::raw("'".$type."' as type"), 'ac.detection_profile_id as detection_profile_id', 'name');

            if ($union) {
                $query = $query->unionAll($q);
            }
            else {
                $query = $q;
                $union = true;
            }
        }

        return AutomationConfigResource::collection($query->get());
    }

    public function updateAutomations(DetectionProfile $profile) {
        $type = request()->get('type');
        $id = request()->get('id');
        $value = request()->get('value');

        if ($value == 'true') {
            $count = DB::table('automation_configs')->where([
                ['detection_profile_id', '=', $profile->id],
                ['automation_config_id', '=', $id],
                ['automation_config_type', '=', $type],
            ])->count();

            if ($count == 0) {
                DB::table('automation_configs')->insert([
                    'detection_profile_id' => $profile->id,
                    'automation_config_id' => $id,
                    'automation_config_type' => $type,
                ]);
            }
        }
        else {
            DB::table('automation_configs')->where([
                ['detection_profile_id', '=', $profile->id],
                ['automation_config_id', '=', $id],
                ['automation_config_type', '=', $type],
            ])->delete();
        }

        return true;
    }

}