<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ajency\FileUpload\models\FileUpload_Varients;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Image;
use Carbon\Carbon;

class FileUpload_Photos extends Model
{
    use SoftDeletes;
    protected $table = 'fileupload_photos';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public function mapping()
    {
        return $this->morphMany('Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }
    public function upload($image,$type, $obj_instance, $obj_class, $watermark, $public,$base64_file,$base64_file_ext,$imageName)
    {
        $config        = config('ajfileupload');
        $imageFileName = time();

        $disk          = \Storage::disk($config['disk_name']);
        $ext           = ($base64_file_ext == "")?$image->getClientOriginalExtension():$base64_file_ext;
        if($base64_file != "")
            $image = $base64_file;
        \Log::debug("disk_name==".$config['disk_name']);
        \Log::debug("obj_class==".$obj_class);
        \Log::debug("obj_instance==".$obj_instance);
        \Log::debug("isset==".isset($config['model'][$obj_class]));
        if (isset($config['model'][$obj_class])) {
            if($imageName == "")
                $filepath = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/images/' . $imageFileName . '/' . $obj_instance[$config['model'][$obj_class]['slug_column']] . '-';
            else
                $filepath = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/';
        } else {
            if($imageName == "")
                $filepath = $config['default_base_path'] . 'images/' . $imageFileName . '/image-';
            else
                $filepath = $config['default_base_path'];
        }
        if($imageName == "")
            $fp = $filepath . 'original.' . $ext;
        else
            $fp = $filepath . $imageName. ".". $ext;
        
        if ($disk->put($fp, file_get_contents($image), 'private')) {
            $this->url = $disk->url($fp);
            $this->save();
            \Log::debug("fp==".$fp);
        } else {
            return false;
        }


        if (isset($config['model'][$obj_class]) && $base64_file =="") {
            $img = Image::make($image->getRealPath());
            foreach ($config['model'][$obj_class]['sizes'][$type] as $size_name) {
                if (isset($config['sizes'][$size_name])) {
                    $new_img = Image::make($image->getRealPath());
                    $new_img->resize($config['sizes'][$size_name]['width'], $config['sizes'][$size_name]['height'], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    if ($watermark and isset($config['sizes'][$size_name]['watermark'])) {
                    	$path = $config['sizes'][$size_name]['watermark']['image_path'];
                    	$pos = $config['sizes'][$size_name]['watermark']['position'];
                        $new_img->insert($config['sizes'][$size_name]['watermark']['image_path'],
                            $config['sizes'][$size_name]['watermark']['position'],
                            $config['sizes'][$size_name]['watermark']['x'],
                            $config['sizes'][$size_name]['watermark']['y']
                        );
                    }
                    $new_img = $new_img->stream();
                    $fp      = $filepath . $size_name .'.'. $ext;
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
                    $entry = new FileUpload_Varients;
                    $entry->photo_id = $this->id;
                    $entry->size = $size_name;
                    $entry->url = $disk->url($fp);
                    $entry->save();
                }
            }
        }
        return true;
    }

    public function generateResizedImages($object_id,$presets,$depth,$obj_class,$obj_instance,$filename){
        // $filepath = $this->url;
        $config        = config('ajfileupload');
        $disk          = \Storage::disk($config['disk_name']);
        $path = explode('amazonaws.com/',$this->url);
        // $filepath = \Storage::disk('s3')->temporaryUrl( $this->url, \Carbon::now()->addMinutes(5) );
        $command = $disk->getDriver()->getAdapter()->getClient()->getCommand('GetObject', [
            'Bucket'                     => config('filesystems.disks.s3.bucket'),
            'Key'                        => $path[1],
            //'ResponseContentDisposition' => 'attachment;'//for download
        ]);
        $filepath =  $disk->getDriver()->getAdapter()->getClient()->createPresignedRequest($command, '+10 minutes')->getUri();
        echo "filepath===".$filepath;
        
        // echo "path===".$path[1]."<br/>";
        // $filepath = $path[1];
        // $files = \Storage::disk($config['disk_name'])->allFiles();
        // foreach($files as $file){
        //     echo "file===".$file;
        // }
        // dd("exit");
        // echo $filepath."<br/><br/>";
        
        $extarr = explode(".", $filepath);
        $ext = (count($extarr)>1)?$extarr[1]:"jpg";
        
        $image_size = ($presets == "original")?$presets:($presets."$$".$depth);
        if($this->image_size != null){
            $image_size_arr = json_decode($this->image_size,true);
            if(is_array($image_size_arr))
                array_push($image_size_arr, $image_size);
        }
        else {
            $image_size_arr = [$image_size];
        }
        $this->image_size = json_encode($image_size_arr);
        if($presets == "original"){
            $nfilepath = explode("?", $filepath)[0];
            $newfilepath = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/'.$presets.'/' .$filename;
            $newfilepathfullurl = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/', $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$presets.'/', $this->url);
            if($disk->put($newfilepath, file_get_contents($filepath), 'public')) {
                $this->save();
                return $newfilepathfullurl;
            } else {
                return false;
            }
        }
        else{
            $config_dimensions = $config['presets'][$presets][$depth];
            $dimensions_arr = explode("X", $config_dimensions);
            $width = $dimensions_arr[0];
            $height = $dimensions_arr[1];
            $new_img = Image::make(file_get_contents($filepath));
            $new_img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $new_img = $new_img->stream();

            $fp      = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/'.$presets.'/' .$depth.'/'.$filename;
            if ($disk->put($fp, $new_img->__toString(), 'public')) {
                $this->save();
                $newfilepathfullurl = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/', $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$presets.'/' .$depth.'/', $this->url);
                return $newfilepathfullurl;
            } else {
                return false;
            }

        }
        


    }

    public function returnPresetUrls($presets,$obj_class,$obj_instance){
        $resp = [];
        $config        = config('ajfileupload');
        foreach($presets as $preset){       
            foreach($config['presets'] as $cpreset => $cdepths){
                // echo "cpreset=".$cpreset."<br/>";
                if(in_array($cpreset, $presets)){
                    $cdepth_data = [];
                    $path = explode('amazonaws.com/',$this->url);
                    foreach($cdepths as $cdepth => $csizes){
                        
                        // $newfilepath = url('/'.$config['model'][$obj_class]['base_path'].'/'.$this->id.'/'.$cpreset.'/'.$cdepth.'/',]);

                        $newfilepath = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/',$config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$cpreset.'/'.$cdepth.'/', $path[1]);
                        if($config['use_cdn'] && $config['cdn_url'] ){
                            $tempUrl = parse_url($newfilepath);
                            $newfilepath =  $config['cdn_url'] .'/'. $tempUrl['path'];
                        }
                        // echo "newfilepath=".$newfilepath;
                        $cdepth_data[$cdepth] = url($newfilepath);
                    }
                    if($cpreset == "original"){
                        $newfilepath = str_replace($config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/',$config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']].'/'.$cpreset.'/', $path[1]);
                        if($config['use_cdn'] && $config['cdn_url'] ){
                            $tempUrl = parse_url($newfilepath);
                            $newfilepath =  $config['cdn_url'] .'/'. $tempUrl['path'];
                        }
                        $resp[$cpreset] = url($newfilepath);
                    }
                    else{
                        $resp[$cpreset] = $cdepth_data;
                    }
                }
            }
        }
        return $resp;
    }
}
