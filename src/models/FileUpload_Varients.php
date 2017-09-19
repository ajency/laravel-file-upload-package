<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileUpload_Varients extends Model
{
	use SoftDeletes;
	protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];
    protected $table = 'fileupload_varients';
}