<?php

namespace App\Http\Controllers;

use App\Bot;
use App\Http\Requests\BotCreationRequest;
use Illuminate\Http\Request;

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
     * Show the list of available bots
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bot.index');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('bot.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BotCreationRequest $request)
    {
        $bot = Bot::create($request->all());

        return redirect()->route('bot.show', [$bot]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Bot  $bot
     * @return \Illuminate\Http\Response
     */
    public function show(Bot $bot)
    {
        dd($bot);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Bot  $bot
     * @return \Illuminate\Http\Response
     */
    public function edit(Bot $bot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Bot  $bot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bot $bot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Bot  $bot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bot $bot)
    {
        //
    }
}
