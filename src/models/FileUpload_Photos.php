<?php
namespace Ajency\FileUpload\Models;
use Illuminate\Database\Eloquent\Model;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Image;
class FileUpload_Photos extends Model
{
	use SoftDeletes;
    protected $table = 'fileuploads_photos';
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];
    public function mapping(){
        return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }
    
}