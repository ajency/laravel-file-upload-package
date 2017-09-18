<?php

namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Image;

class FileUpload_Photos extends Model
{
    protected $table = 'fileuploads_photos';

    

    public function mapping(){
        return $this->morphMany( 'Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }
    public function uploadImage($image,$is_watermarked,$model_obj,$classname,$sizes=[],$watermark=null)
    {

        $imageFileName = time();
        $s3            = \Storage::disk('s3');
        $filePath      = $classname.'/images/' . $imageFileName;
        $ext = $image->getClientOriginalExtension();

        $resp = $s3->put($filePath. '/original.' . $ext , file_get_contents($image), 'private');

        $config = config('ajfileupload');

        $img = Image::make($image->getRealPath());
        //if(isset($config['watermark']['path']) and $config['watermark']['path']!='' and $config['watermark']['path']!=null ){
        if($is_watermarked == true and $watermark != null and $watermark !=''){
            $pos = (isset($watermark['position']))? $watermark['position']: 'bottom-left';
            $x = (isset($watermark['x']))? $watermark['x']: 10;
            $y = (isset($watermark['y']))? $watermark['y']: 10;
            $img->insert($watermark['path'], $pos, $x, $y);
            $image = $img;
            $water = $img->stream();
            $resp = $s3->put($filePath. '/watermark.' . $ext, $water->__toString(), 'public');

        }
        // if (isset($config['sizes'])){
            foreach($sizes as $key => $size){
                $img = $image;
                $img->resize($size['width'], $size['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img = $img->stream();
                $resp = $s3->put($filePath. '/'.$key.'.' . $ext, $img->__toString(), 'public');
            }
        // }
        
        $this->url = $s3->url($filePath);
        $this->save();
        // return response()->json($s3->url($filePath.'/resize.'.$image->getClientOriginalExtension()));
    }

}
