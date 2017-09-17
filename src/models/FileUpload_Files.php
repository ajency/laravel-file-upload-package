<?php

namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Ajency\FileUpload\models\FileUpload_Mapping;

class FileUpload_Photos extends Model
{
    protected $table = 'fileuploads_files';

    

    public function mapping(){
        return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }

    public function uploadFile($file,$classname){
    	$fileName = time();
        $s3            = \Storage::disk('s3');
        $filePath      = $classname.'/files/' . $fileName;
        $ext = $file->getClientOriginalExtension();
        $resp = $s3->put($filePath. '.' . $ext , file_get_contents($file), 'public');

         $this->url = $s3->url($filePath.$ext);
        $this->save();
    }

}