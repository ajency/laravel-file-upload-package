<?php
namespace Ajency\FileUpload;
use Ajency\FileUpload\models\FileUpload_Photos;
use Ajency\FileUpload\models\FileUpload_Files;
use Illuminate\Database\Eloquent\Collection;
trait FileUpload{
	public function media(){
		return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'object');
	}

	private function validatefile($file,$type){
		if($type = 0) $valid = config('ajfileupload')['valid_image_formats'];
		else $valid = config('ajfileupload')['valid_file_formats'];
		$ext = $image->getClientOriginalExtension();
		if (!in_array($ext, $valid)) return false;
		return true;
	}
	public function uploadImage($image,$is_watermarked=true,$is_public=true,$alt='',$caption='',$name=""){
		if(!validateImage($image,0)) return false;
		$upload = new FileUpload_Photos;
        $upload->name = $name;
        $upload->slug = str_slug($name);
        $upload->is_public = $is_public;
        $upload->alt_text = $alt;
        $upload->caption = $caption;
        $upload->save();
        $url = $upload->upload($image,$this,self::class,$is_watermarked,$is_public);
        return $url;
	}


}