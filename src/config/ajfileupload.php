<?php

return [
	'disk_name' => 's3',
	'base_root_path' => '',
	'default_base_path' => 'other_files',
	'valid_image_formats' => ['jpg', 'png', ],
	'valid_file_formats' => ['doc', 'docx', 'pdf'],
	'sizes' => [
		'thumb' => [
			'width' => 100,
			'height' => 56,
			'watermark' => [
				'image_path' => public_path().'/img/logo.png',
				'position'=>'bottom-right', 
				'x'=> 10, 
				'y'=>10
			],
		],
	],
	'model' => [
		'App\Model_name' => [
			'base_path' => 'model/',
			'slug_column' => 'slug',
			'sizes' => ['thumb']
		],
	],
];