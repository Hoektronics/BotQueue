<?php

namespace App\Http\Controllers;

use App\Enums\FileTypeEnum;
use App\File;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Str;

class FileController extends Controller
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
        $files = Auth::user()->files;

        return view('file.index', [
            'files' => $files,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('file.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FileUploadRequest $request)
    {
        $originalFile = $request->file('file');
        $clientOriginalName = $originalFile->getClientOriginalName();

        $extension = FileFacade::extension($clientOriginalName);
        $newName = Str::random(40) . '.' . $extension;
        $uploadedFilePath = $originalFile->storePubliclyAs('uploads/'.Auth::user()->id, $newName);

        $file = File::create([
            'path' => $uploadedFilePath,
            'name' => $clientOriginalName,
            'filesystem' => config('filesystems.default'),
            'type' => FileTypeEnum::fromExtension($extension),
            'uploader_id' => Auth::id(),
        ]);

        return redirect()->route('jobs.create.file', [$file]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        dd($file);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
