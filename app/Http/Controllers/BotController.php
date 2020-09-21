<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Cluster;
use App\Http\Requests\BotCreationRequest;
use App\Http\Requests\BotUpdateRequest;
use App\Services\BotDriverService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
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
     * Show the list of available bots.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bot.index', [
            'bots' => Auth::user()->bots()->with('cluster', 'currentJob')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clusters = Auth::user()->clusters;

        return view('bot.create', [
            'clusters' => $clusters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(BotCreationRequest $request)
    {
        /** @var Cluster $cluster */
        $cluster = Cluster::query()->find($request->get('cluster'));

        /** @var Bot $bot */
        $bot = new Bot([
            'name' => $request->get('name'),
            'type' => $request->get('type'),
            'creator_id' => Auth::id(),
            'cluster_id' => $cluster->id,
        ]);

        $bot->save();

        return redirect()->route('bots.show', [$bot]);
    }

    /**
     * Display the specified resource.
     *
     * @param Bot $bot
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function show(Bot $bot)
    {
        $this->authorize('view', $bot);

        $bot->load(['cluster', 'creator']);

        return view('bot.show', [
            'bot' => $bot,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Bot $bot
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function edit(Bot $bot)
    {
        $this->authorize('update', $bot);

        return view('bot.edit', [
            'bot' => $bot,
            'hosts' => Auth::user()->hosts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BotUpdateRequest $request
     * @param BotDriverService $driverService
     * @param Bot $bot
     * @return void
     * @throws AuthorizationException
     */
    public function update(BotUpdateRequest $request, BotDriverService $driverService, Bot $bot)
    {
        $this->authorize('update', $bot);

        $bot->host_id = $request->get('host', $bot->host_id);
        $bot->name = $request->get('name', $bot->name);

        if ($request->has('driver')) {
            $driverService->driver_type = $request->get('driver');
            $driverService->serial_port = $request->get('serial_port');
            $driverService->baud_rate = $request->get('baud_rate');
            $driverService->command_delay = $request->get('command_delay');

            $bot->driver = $driverService->encode();
        }

        $bot->save();

        return redirect()->route('bots.show', [$bot]);
    }

    public function delete(Bot $bot)
    {
        return view('bot.delete', [
            'bot' => $bot,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Bot $bot
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Bot $bot)
    {
        $bot->delete();

        return redirect()->route('bots.index');
    }
}
