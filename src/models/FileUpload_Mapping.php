<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileUpload_Mapping extends Model
{
    use SoftDeletes;
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
