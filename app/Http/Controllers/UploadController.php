<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'checksum'       => 'required|string',
            'chunk_index'    => 'required|integer|min:0',
            'total_chunks'   => 'required|integer|min:1',
            'file'           => 'required|file',
            'original_name'  => 'required|string',
        ]);

        $upload = Upload::firstOrCreate(
            ['checksum' => $request->checksum],
            [
                'original_name'   => $request->original_name,
                'total_chunks'    => $request->total_chunks,
                'uploaded_chunks' => 0,
                'status'          => 'uploading',
            ]
        );

        $chunkDir = "uploads/{$upload->checksum}";
        $chunkPath = "{$chunkDir}/chunk_{$request->chunk_index}";

      
        if (!Storage::disk('local')->exists($chunkPath)) {
            Storage::disk('local')->put(
                $chunkPath,
                file_get_contents($request->file('file')->getRealPath())
            );

            $upload->increment('uploaded_chunks');
        }

        return response()->json([
            'message' => 'Chunk uploaded',
            'uploaded_chunks' => $upload->uploaded_chunks,
            'total_chunks' => $upload->total_chunks,
        ]);
    }

   
    public function completeUpload(Request $request)
    {
        $request->validate([
            'checksum' => 'required|string'
        ]);

        $upload = Upload::where('checksum', $request->checksum)->lockForUpdate()->first();

        if (!$upload) {
            return response()->json(['error' => 'Upload not found'], 404);
        }

        if ($upload->status === 'completed') {
            return response()->json([
                'message' => 'Already completed',
                'stored_name' => $upload->stored_name
            ]);
        }

        DB::beginTransaction();

        try {
            $storedName = uniqid() . '_' . $upload->original_name;
            $finalRelativePath = "uploads/{$storedName}";
            $finalFullPath = Storage::disk('public')->path($finalRelativePath);

           
            if (!file_exists(dirname($finalFullPath))) {
                mkdir(dirname($finalFullPath), 0755, true);
            }

            $finalHandle = fopen($finalFullPath, 'ab');

            for ($i = 0; $i < $upload->total_chunks; $i++) {
                $chunkPath = storage_path("app/uploads/{$upload->checksum}/chunk_{$i}");

                if (!file_exists($chunkPath)) {
                    throw new \Exception("Missing chunk {$i}");
                }

                fwrite($finalHandle, file_get_contents($chunkPath));
            }

            fclose($finalHandle);

           
            Storage::disk('local')->deleteDirectory("uploads/{$upload->checksum}");

            $upload->update([
                'stored_name' => $storedName,
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Upload completed successfully',
                'stored_name' => $storedName
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
