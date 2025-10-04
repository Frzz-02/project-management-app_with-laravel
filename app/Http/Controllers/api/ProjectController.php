<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\Project as ResourcesProject;
use App\Http\Resources\ProjectResource;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Project::class);
        $projects = Project::all();
        return response()->json([
            'title' => 'List of Projects',
            'data' => ProjectResource::collection($projects)
        ], 200);
    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData["created_by"] = auth()->user()->id;
        $validatedData["deadline"] = Carbon::parse($validatedData["deadline"])->format('Y-m-d');
        
        
        $project = Project::create($validatedData);
        return response()->json([
            'message' => 'berhasil menyimpan data !',
            'data' => new ProjectResource($project)
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        // dd($project->with('boards')->find($project->id));
        
        return response()->json([
            'title' => 'Detail of Project',
            'data' => new ProjectResource($project->loadMissing(['boards', 'members.user']) )
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $validatedData = $request->validated();
        if (isset($validatedData['deadline'])) {
            $validatedData["deadline"] = Carbon::parse($validatedData["deadline"])->format('Y-m-d');
        }

        $project->update($validatedData);
        return response()->json([
            'message' => 'berhasil mengubah data !',
            'data' => new ProjectResource($project)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json([
            'message' => 'berhasil menghapus data !',
        ], 200);
    }
}
