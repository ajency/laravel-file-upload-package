<?php
namespace Ajency\FileUpload\Models;
use Illuminate\Database\Eloquent\Model;
use Ajency\FileUpload\models\FileUpload_Mapping;
class FileUpload_Files extends Model
{
	use SoftDeletes;
    protected $table = 'fileuploads_files';
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];
    public function mapping(){
        return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }
    public function upload($file, $obj_instance, $obj_class, $public){
    	$config        = config('ajfileupload');
    	$name= $this->slug.'-'.time().'.'.$file->getClientOriginalExtension();
    	$disk          = \Storage::disk($config['disk_name']);
        $ext           = $file->getClientOriginalExtension();
        if (isset($config['model'][$obj_class])) {
            $filepath = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/files/' .$obj_instance[$config['model'][$obj_class]['slug_column']].'-'. $name ;
        } else {
            $filepath = $config['default_base_path'] . 'files/' . $name 
        }
        if ($public) {
	    	if ($disk->put($fp, $new_img->__toString(), 'public')) {
	            $this->save();
	        } else {
	            return false;
	        }
	    } else {
	        if ($disk->put($fp, $new_img->__toString(), 'private')) {
	            $this->save();
	        } else {
	            return false;
	        }
	    }
	    $this->url = $disk->url($fp);
        $this->save();
        return true;
    }
}