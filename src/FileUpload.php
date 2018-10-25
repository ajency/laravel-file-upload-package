<?php
namespace Ajency\FileUpload;
use Ajency\FileUpload\models\FileUpload_Photos;
use Ajency\FileUpload\models\FileUpload_Files;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Ajency\FileUpload\models\FileUpload_Varients;
use Illuminate\Database\Eloquent\Collection;
use DB;
trait FileUpload{
	public function media(){
		return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'object');
	}

	private function validatefile($file,$type){
		if($type == 0) $valid = config('ajfileupload')['valid_image_formats'];
		else $valid = config('ajfileupload')['valid_file_formats'];
		$ext = strtolower($file->getClientOriginalExtension());
		if (!in_array($ext, $valid)) return false;
		return true;
	}
	public function uploadImage($image,$type,$is_watermarked=true,$is_public=true,$alt='',$caption='',$name="",$base64_file="",$base64_file_ext="",$imageName="",$attributes=[]){
		\Log::debug("uploadImage===");
		if($base64_file =="")
			if(!$this->validatefile($image,0)) 
				return false;
		$upload = new FileUpload_Photos;
        $upload->name = $name;
        $upload->slug = str_slug($name);
        $upload->is_public = $is_public;
        $upload->alt_text = $alt;
        $upload->caption = $caption;
        if($base64_file !=""){
        	$upload->image_size = json_encode(["original"]);
        	$upload->dimensions = json_encode(["original_width" => $image_size[0],"original_height" => $image_size[1]]);
        	$upload->photo_attributes =json_encode($attributes);
        }
        $upload->save();
        if($upload->upload($image,$type,$this,get_class($this),$is_watermarked,$is_public,$base64_file,$base64_file_ext,$imageName)){
     	   return $upload->id;
    	}else{
    		return false;
    	}
	}

	public function unmapAllImages(){
		$map_images = FileUpload_Mapping::where([['object_type',get_class($this)],['object_id',$this->id],['file_type',FileUpload_Photos::class]])->select('file_id')->get();
		foreach($map_images as $map_image){
			$this->unmapImage($map_image["file_id"]);
		}
	}

	public function mapImage($image_id,$type){
		$check = FileUpload_Mapping::where('file_id',$image_id)->where('file_type',FileUpload_Photos::class)->count();
		if ($check>0) return false;
		$image = FileUpload_Photos::find($image_id);
        $mapping = new FileUpload_Mapping;
        $mapping->type = $type;
        $this->media()->save($mapping);
        $image->mapping()->save($mapping);
        return true;
	}
	public function unmapImage($image_id){
		return $this->media()->where('file_type',FileUpload_Photos::class)->where('file_id',$image_id)->delete();
	}
	public function remapImages($images,$type){
		// add n remove
		$curr_images = $this->media()->where('file_type',FileUpload_Photos::class)->where('type',$type)->pluck('file_id')->toArray();
		$additions = array_diff($images,$curr_images);
		$deletions = array_diff($curr_images,$images);
		foreach($deletions as $file){
			$this->unmapImage($file);
		}
		foreach($additions as $file){
			$this->mapImage($file,$type);
		}
		// foreach($images as $image){
		// 	$this->mapImage($image);
		// }
	}
	public function getImages($type){
		$uploads = array();
		$images = $this->media()->where('file_type',FileUpload_Photos::class)->where('type',$type)->pluck('id')->toArray();
		$images = FileUpload_Mapping::whereIn('id',$images)->get();
		foreach ($images as $image) {
			$uploads[$image->file_id] = array('id'=>$image->file_id);
			$details = FileUpload_photos::where('id',$image->file_id)->first();
			if(!empty($details)){
				$uploads[$image->file_id]['name'] = $details->name;
				$uploads[$image->file_id]['caption'] = $details->caption;
				$uploads[$image->file_id]['alt'] = $details->alt_text;
				$varients = FileUpload_Varients::where('photo_id',$image->file_id)->get();
				foreach ($varients as $varient) {
					$uploads[$image->file_id][$varient->size]=$varient->url;
				}
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
	public function mapFile($file_id,$type){
		$check = FileUpload_Mapping::where('file_id',$file_id)->where('file_type',FileUpload_Files::class)->count();
		if ($check>0) return false;
		$file = FileUpload_Files::find($file_id);
        $mapping = new FileUpload_Mapping;
        $mapping->type = $type;
        $this->media()->save($mapping);
        $file->mapping()->save($mapping);
        return true;
	}
	public function unmapFile($file_id){
		$file =$this->media()->where('file_type',FileUpload_Files::class)->where('file_id',$file_id)->delete();
	}
	public function remapFiles($files,$type){
		//add n remove
		$curr_files = $this->media()->where('file_type',FileUpload_Files::class)->where('type',$type)->pluck('file_id')->toArray();
		$additions = array_diff($files,$curr_files);
		$deletions = array_diff($curr_files,$files);
		foreach($deletions as $file){
			$this->unmapFile($file);
		}
		foreach($additions as $file){
			$this->mapFile($file,$type);
		}
	}
	public function getFiles($type){
		$uploads = array();
		$files = $this->media()->where('file_type',FileUpload_Files::class)->where('type',$type)->pluck('file_id')->toArray();
		$files = FileUpload_Files::whereIn('id',$files)->get();
		if(!empty($files)){
			foreach ($files as $file) {
				$uploads[$file->id] = array('id'=>$file->id);
				$uploads[$file->id]['name'] = $file->name; 
				$uploads[$file->id]['url'] = $file->url; 
				$uploads[$file->id]['size'] = $file->size; 
			}
		}
		return $uploads;
	}
	public function getAllFilesByType(){
		$uploads = array();
		$files = $this->media()->where('file_type',FileUpload_Files::class)->get(); 
		if(!empty($files)){
			foreach ($files as $file) {
				$fileObj = FileUpload_Files::find($file->file_id);

				$uploads[$file->type] = array('id'=>$fileObj->id);
				$uploads[$file->type]['name'] = $fileObj->name; 
				$uploads[$file->type]['url'] = $fileObj->url; 
				$uploads[$file->type]['size'] = $fileObj->size; 
			}
		}
		return $uploads;
	}

	public function renameFile($file){
		// dd($file);
		$obj = FileUpload_Files::find($file['id']);
		$obj->name = $file['name'];
		$obj->save();
	}

	public function getSingleFile($file_id){
		$obj = FileUpload_Files::find($file_id);
		if(config('ajfileupload')['disk_name'] == "s3"){
			$path = explode('amazonaws.com/',$obj->url);
			return \Storage::disk(config('ajfileupload')['disk_name'])->get('/'.$path[1]);
		}
	}

	public function getSingleImage($presets,$depth){
		$map_image = FileUpload_Mapping::where([['object_type',get_class($this)],['object_id',$this->id],['file_type',FileUpload_Photos::class]])->first();	
		// print_r($map_image);exit;
		// dd(DB::getQueryLog());
		// dd($map_image->file);
		$config        = config('ajfileupload');
		if($map_image == null) return false;
		if(config('ajfileupload')['disk_name'] == "s3"){
			$map_image_size = json_decode($map_image->file->image_size,true);
			// dd($map_image->image_size);
			if($map_image_size == null) return false;
			$image_size = ($presets == "original")?$presets:($presets."$$".$depth);
			echo "map_image_size===";
			print_r($map_image_size);
			echo "image_size===";
			print_r($image_size);
			// dd("exit");
			if(in_array($image_size,$map_image_size)){
				$obj_instance = $this;
				$obj_class = get_class($this);
				if($presets == "original"){
					$newfilepath = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/', $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$presets.'/', $map_image->file->url);
				}
				else{
					$newfilepath = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/', $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$presets.'/', $map_image->file->url);
				}
				
				return $newfilepath;
			}
			else
				return false;
			// $path = explode('amazonaws.com/',$map_image->file->url);
			
			// return \Storage::disk(config('ajfileupload')['disk_name'])->get('/'.$path[1]);
		}
	}

	public function resizeImages($presets,$depth,$filename){
		echo "enters resizeImages";
		$filePhotoObj = FileUpload_Mapping::where([['object_type',get_class($this)],['object_id',$this->id],['file_type',FileUpload_Photos::class]])->first();
		return $filePhotoObj->file->generateResizedImages($this->id,$presets,$depth,get_class($this),$this,$filename);
	}

}