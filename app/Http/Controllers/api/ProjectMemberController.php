<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProjectMember;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;

class ProjectMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = ProjectMember::with('user:id,name,email')->get();
        return response()->json([
            'title' => 'List of Project Members',
            'data' => $members
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectMemberRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectMember $projectMember)
    {
        // $members = ProjectMember::with('user:id,username,full_name')->find($projectMember->project_id);
        // if (!$members) {
        //     return response()->json([
        //         'message' => 'Project Member not found'
        //     ], 200);
        // }

        $members = $projectMember->with('user:id,username,full_name,email')->find($projectMember->project_id);
        // dd($members);
        return response()->json([
            'title' => 'Detail of Project Member',
            'data' => new \App\Http\Resources\ProjectMemberResource($members)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectMember $projectMember)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectMemberRequest $request, ProjectMember $projectMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectMember $projectMember)
    {
        //
    }
}
