<?php
namespace Ajency\FileUpload;
use Ajency\FileUpload\models\FileUpload_Photos;
use Ajency\FileUpload\models\FileUpload_Files;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Ajency\FileUpload\models\FileUpload_Varients;
use Illuminate\Database\Eloquent\Collection;
trait FileUpload{
	public function media(){
		return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'object');
	}

	private function validatefile($file,$type){
		if($type == 0) $valid = config('ajfileupload')['valid_image_formats'];
		else $valid = config('ajfileupload')['valid_file_formats'];
		$ext = $file->getClientOriginalExtension();
		if (!in_array($ext, $valid)) return false;
		return true;
	}
	public function uploadImage($image,$is_watermarked=true,$is_public=true,$alt='',$caption='',$name=""){
		if(!$this->validatefile($image,0)) return false;
		$upload = new FileUpload_Photos;
        $upload->name = $name;
        $upload->slug = str_slug($name);
        $upload->is_public = $is_public;
        $upload->alt_text = $alt;
        $upload->caption = $caption;
        $upload->save();
        if($upload->upload($image,$this,self::class,$is_watermarked,$is_public)){
     	   return $upload->id;
    	}else{
    		return false;
    	}
	}
	public function mapImage($image_id){
		$check = FileUpload_Mapping::where('file_id',$image_id)->where('file_type',FileUpload_Photos::class)->count();
		if ($check>0) return false;
		$image = FileUpload_Photos::find($image_id);
        $mapping = new FileUpload_Mapping;
        $this->media()->save($mapping);
        $image->mapping()->save($mapping);
        return true;
	}
	public function mapImages($images){
		foreach($images as $image){
			$this->mapImage($image);
		}
	}
	public function getImages(){
		$uploads = array();
		$images = $this->media()->where('file_type',FileUpload_Photos::class)->pluck('id')->toArray();
		$images = FileUpload_Mapping::whereIn('id',$images)->get();
		foreach ($images as $image) {
			$uploads[$image->file_id] = array('id'=>$image->file_id);
			$varients = FileUpload_Varients::where('photo_id',$image->file_id)->get();
			foreach ($varients as $varient) {
				$uploads[$image->file_id][$varient->size]=$varient->url;
			}
		}
		return $uploads;
	}




	public function uploadFile($file,$is_public=true,$name=""){
		if(!$this->validatefile($file,1)) return false;
		$upload = new FileUpload_Files;
        $upload->name = $name;
        $upload->slug = str_slug($name);
        $upload->is_public = $is_public;
        $upload->save();
        if($upload->upload($file,$this,self::class,$is_public)){
     	   return $upload->id;
    	}else{
    		return false;
    	}
	}
	public function mapFile($file_id){
		$check = FileUpload_Mapping::where('file_id',$file_id)->where('file_type',FileUpload_Files::class)->count();
		if ($check>0) return false;
		$file = FileUpload_Files::find($file_id);
        $mapping = new FileUpload_Mapping;
        $this->media()->save($mapping);
        $file->mapping()->save($mapping);
        return true;
	}
	public function mapFiles($images){
		foreach($files as $file){
			$this->mapFile($file);
		}
	}
	public function getFiles(){
		$uploads = array();
		$files = $this->media()->where('file_type',FileUpload_Files::class)->pluck('id')->toArray();
		$files = FileUpload_Files::whereIn('id',$files)->get();
		foreach ($files as $file) {
			$uploads[$file->id] = array('id'=>$file->id);
			$uploads[$file->id]['url'] = $file->url; 
		}
		return $uploads;
	}

}