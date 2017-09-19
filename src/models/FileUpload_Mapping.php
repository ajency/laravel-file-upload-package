<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload_Mapping extends Model
{
    protected $table = 'fileupload_mapping';
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];
    public function object()
    {
        return $this->morphTo();
    }
    public function file()
    {
        return $this->morphTo();
    }
}
