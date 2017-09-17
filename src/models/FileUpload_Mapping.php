<?php

namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Image;

class FileUpload_Mapping extends Model
{
    protected $table = 'fileuploads_mapping';

    public function object()
    {
        return $this->morphTo();
    }

    public function file()
    {
        return $this->morphTo();
    }

}
