# Ajency Photos and Docs Package

## Installation
1. install GD and Imagick to your server
```
sudo apt-get install php7.0-gd
sudo apt-get install php7.0-imagick
sudo service apache2 restart
```
2. Install following packages to your project
	- intervention/image:^2.4
	- league/flysystem-aws-s3-v3:^1.0 (https://packagist.org/packages/league/flysystem-aws-s3-v3)

3. Create a folder /packages/ajency/laravel-file-upload-package Clone this repo in the folder in your Laravel project
4. In main project composer.json -> autoload -> psr-4 add "Ajency\\FileUpload\\": "packages/ajency/laravel-file-upload-package/src"
5. In config/app.php add 'Ajency\FileUpload\FileUploadServiceProvider' under providers
6. In config/app.php add 'AjFileUpload' => Ajency\FileUpload\FileUploadServiceProvider under aliases
7. Update .env file with S3 details
```
AWS_KEY=
AWS_SECRET=
AWS_REGION=
AWS_BUCKET=
```
8. Run composer dump-autoload
9. Run php artisan vendor:publish
10. Run php artisan migrate

##Usage

1. Add the trait to your model and override the model constructor
```php
class TableName extends Model { 
  use FileUpload;

    
    public function __construct(array $attributes = array())
	{
	    parent::__construct($attributes);

	    self::$size_conversion = [
	        'thumb' => [
	            'height' => 56, 
	            "width" => 100,
	        ],
	    ];

	    self::$watermark = [
	        'path'     => public_path() . '/img/facebook.png',
	        'position' => 'bottom-right',
	        'x'        => 10,
	        'y'        => 10,
	    ];

	    self::$formats = ['image' => ["jpg","png",], 'files' => ["doc","docx","pdf"]];
	}

}
```php