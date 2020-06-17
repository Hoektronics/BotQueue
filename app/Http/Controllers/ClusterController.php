<?php

namespace App\Http\Controllers;

use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Http\Requests\ClusterCreationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ClusterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clusters = Auth::user()->clusters()->withCount('bots')->get();

        return view('cluster.index', compact('clusters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cluster.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ClusterCreationRequest $request
     * @return Response
     */
    public function store(ClusterCreationRequest $request)
    {
        /** @var Cluster $cluster */
        $cluster = Cluster::create([
            'name' => $request->get('name'),
            'creator_id' => Auth::id(),
        ]);

        return redirect()->route('clusters.show', $cluster);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cluster $cluster
     * @return \Illuminate\Http\Response
     */
    public function show(Cluster $cluster)
    {
        $cluster->load(['bots', 'creator']);

        return view('cluster.show', [
            'cluster' => $cluster,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cluster $cluster
     * @return \Illuminate\Http\Response
     */
    public function edit(Cluster $cluster)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Cluster $cluster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cluster $cluster)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cluster $cluster
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cluster $cluster)
    {
        //
    }
}
