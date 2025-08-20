<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SFTPFileUploadController extends Controller
{
    /**
     * Show upload form (optional)
     */
    public function index()
    {
        return view('sftp/sftp-upload');
    }

    /**
     * Handle file upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required', // 10MB max
        ]);

        $file = $request->file('file');

        // SFTP তে upload করা
        Storage::disk('sftp')->put(
            $file->getClientOriginalName(),
            file_get_contents($file->getRealPath())
        );

        return back()->with('success', 'File uploaded successfully to SFTP!');
    }
}
