<?php
namespace Ajency\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ajency\FileUpload\models\FileUpload_Varients;
use Ajency\FileUpload\models\FileUpload_Mapping;
use Image;

class FileUpload_Photos extends Model
{
    use SoftDeletes;
    protected $table = 'fileupload_photos';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public function mapping()
    {
        return $this->morphMany('Ajency\FileUpload\models\FileUpload_Mapping', 'file');
    }
    public function upload($image, $obj_instance, $obj_class, $watermark, $public)
    {
        $config        = config('ajfileupload');
        $imageFileName = time();
        $disk          = \Storage::disk($config['disk_name']);
        $ext           = $image->getClientOriginalExtension();
        if (isset($config['model'][$obj_class])) {
            $filepath = $config['base_root_path'] . $config['model'][$obj_class]['base_path'].'/'.$obj_instance[$config['model'][$obj_class]['slug_column']]. '/images/' . $imageFileName . '/' . $obj_instance[$config['model'][$obj_class]['slug_column']] . '-';
        } else {
            $filepath = $config['default_base_path'] . 'images/' . $imageFileName . '/image-';
        }

        $fp = $filepath . 'original.' . $ext;
        if ($disk->put($fp, file_get_contents($image), 'private')) {
            $this->url = $disk->url($fp);
            $this->save();
        } else {
            return false;
        }


        if (isset($config['model'][$obj_class])) {
            $img = Image::make($image->getRealPath());
            foreach ($config['model'][$obj_class]['sizes'] as $size_name) {
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
}
