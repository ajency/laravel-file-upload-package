<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;

class FileuploadMapping extends Model
{
	use SoftDeletes;
	protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];
    protected $table = 'fileuploads_varients';
}