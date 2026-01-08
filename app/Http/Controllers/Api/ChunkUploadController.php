<?php

namespace App\Http\Controllers\Api;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChunkUploadController
{
    public function upload(Request $request)
    {
        $upload = Upload::firstOrCreate(
            ['upload_id' => $request->upload_id],
            [
                'original_name' => $request->file('chunk')->getClientOriginalName(),
                'checksum' => $request->checksum,
                'total_chunks' => $request->total_chunks,
            ]
        );

        $chunkPath = storage_path("app/chunks/{$upload->upload_id}_{$request->chunk_index}");
        file_put_contents($chunkPath, file_get_contents($request->file('chunk')));

        $upload->increment('received_chunks');

        if ($upload->received_chunks == $upload->total_chunks) {
            return $this->finalize($upload);
        }

        return response()->json(['status' => 'chunk received']);
    }

    private function finalize(Upload $upload)
    {
        DB::transaction(function () use ($upload) {
            $finalPath = storage_path("app/uploads/{$upload->upload_id}");
            $out = fopen($finalPath, 'ab');

            for ($i=0; $i<$upload->total_chunks; $i++) {
                fwrite($out, file_get_contents(
                    storage_path("app/chunks/{$upload->upload_id}_{$i}")
                ));
            }

            fclose($out);

            if (hash_file('sha256', $finalPath) !== $upload->checksum) {
                throw new \Exception('Checksum mismatch');
            }

            $upload->update(['completed' => true]);
        });

        return response()->json(['status' => 'completed']);
    }
}
