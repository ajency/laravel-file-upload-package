<?php

namespace Ajency\FileUpload;

use Ajency\FileUpload\models\FileUpload_Photos;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Illuminate\Database\Eloquent\Collection;

trait FileUpload{

	public static $size_conversion = [];

    public static $watermark = null;

    public static $formats = ['image'=>[],'file'=>[]];

	public function media(){
		return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'object');
	}


	public function uploadImage($image,$name="",$slug="",$is_watermarked=true,$is_public=1){
		$ext = $image->getClientOriginalExtension();

		if (!in_array($ext, self::$formats['image'])) return false;

		$mapping = new FileUpload_Mapping;
        $this->media()->save($mapping);

		$upload = new FileUpload_Photos;
        $upload->name = $name;
        $upload->slug = $slug;
        $upload->is_public = $is_public;
        // $upload->addMedia($image)->usingName('msalsajsa')->toMediaCollection('images');
        $upload->save();
        $url = $upload->uploadImage($image,$is_watermarked,$this,self::class,self::$size_conversion,self::$watermark);
        
        $upload->mapping()->save($mapping);
        $url = array();
        foreach (self::$size_conversion as $key => $value) {
        	$url[] = $upload->url.$key.'.'.$ext;
        }
        if(self::$watermark!=null) $url[] = $upload->url.'watermark.' .$ext;
        return $url;

	}

	public function uploadFile($file,$name="",$slug="",$is_watermarked=1,$is_public=1){
		$ext = $file->getClientOriginalExtension();
		if (!in_array($ext, self::$formats['file'])) return false;
		$mapping = new FileUpload_Mapping;
        $this->media()->save($mapping);
        $upload = new FileUpload_Files;
        $upload->name = $name;
        $upload->slug = $slug;
        $upload->is_public = $is_public;
        $upload->save();
        $url = $upload->uploadImage($file,self::class);
        $upload->mapping()->save($mapping);
        $url = array();
        return $upload->url;
	}
}
