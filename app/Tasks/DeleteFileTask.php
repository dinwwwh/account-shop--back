<?php

namespace App\Tasks;

use App\Model\DeleteFile;
use Carbon;
use Storage;
use DB;

class DeleteFileTask
{
    private $config = [
        'deleteSelfAfter' => 49 // hours
    ];

    public function __construct()
    {
        // Delete file
        $files = DeleteFile::where('delete_file_at', '<=', Carbon::now())
            ->where('errors', null)
            ->get();
        foreach ($files as $file) {
            $this->deleteFile($file);
        }

        // Delete self
        $files = DeleteFile::where('deleted_file_at', '<=', Carbon::now()->subDays($this->config['deleteSelfAfter']))
            ->where('errors', null)
            ->get();
        foreach ($files as $file) {
            $this->deleteSelf($file);
        }
    }

    /**
     * To delete file in file path contain
     *
     * @param  mixed $file
     * @return void
     */
    private function deleteFile($file)
    {
        // Initial data
        $file->successes = $file->successes ?? [];

        try {
            // Delete equivalent model 
            $result =  Storage::delete($file->path);
            if (!$result) {
                $file->update([
                    'errors' => 'Không thể xoá file này - result: ' . $result,
                ]);
                return false;
            } else {
                $file->successes[] = 'Đã xoá File thành công.';
                $file->update($file->successes);
            }

            // Delete record in root 
            $result = $file->modelName::destroy($file->modelKey);
            if (!$result) {
                $file->update([
                    'errors' => 'Không thể xoá root model đi kèm - result: ' . $result,
                ]);
                return false;
            } else {
                $file->successes[] = 'Đã xoá Root Model thành công.';
                $file->update($file->successes);
            }

            // confirm deleted
            $file->update([
                'deleted_file_at' => Carbon::now(),
            ]);
        } catch (\Throwable $th) {
            $file->update([
                'errors' => $th,
            ]);
            return false;
        }

        return true;
    }

    /**
     * To delete model 
     *
     * @param  mixed $file
     * @return void
     */
    private function deleteSelf($file)
    {
        try {
            DB::beginTransaction();
            $file->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $file->update([
                'errors' => $th,
            ]);

            return false;
        }

        return true;
    }
}
