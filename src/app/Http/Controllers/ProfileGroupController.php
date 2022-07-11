<?php

namespace App\Http\Controllers;

use App\ProfileGroup;
use App\Resources\ProfileGroupResource;
use Illuminate\Http\Request;

class ProfileGroupController extends Controller
{
    public function index()
    {
        $groups = ProfileGroup::with('detectionProfiles')->orderBy('name')->get();

        return ProfileGroupResource::collection($groups);
    }

    public function make(Request $request)
    {
        $this->validateRequest();

        $group = ProfileGroup::create($request->all());

        return ProfileGroupResource::make($group);
    }

    public function attachProfile(ProfileGroup $group)
    {
        request()->validate([
            'profileId' => 'required|integer',
            'detach' => 'boolean',
        ]);
        $detection_profile_id = request()->get('profileId');
        $detach = request()->get('detach', false);
        if ($detach)
        {
            $group->detectionProfiles()->detach([$detection_profile_id]);
        } else
        {
            $group->detectionProfiles()->attach([$detection_profile_id]);
        }
        return true;
    }

    public function destroy(ProfileGroup $group)
    {
        $group->delete();
        return response()->json(['message' => 'OK'], 200);
    }

    protected function validateRequest()
    {
        $id = 'id';
        if (request()->has('id')) {
            $id = request()->get('id');
        }

        $rules = [
            'name' => "required|unique:profile_groups,name,{$id},id,deleted_at,NULL",
        ];

        request()->validate($rules);
    }
}
