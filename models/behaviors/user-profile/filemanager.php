<?php

use Intervention\Image\ImageManagerStatic as Image;
use yii\helpers\ArrayHelper;
use app\models\File;

return [
    'class' => 'rkit\filemanager\behaviors\FileBehavior',
    'attributes' => [
        'photo' => [
            'storage' => 'localFs',
            'baseUrl' => '@web/uploads',
            'templatePath' => function ($file) {
                $date = new \DateTime(is_object($file->date_create) ? null : $file->date_create);
                return '/' . $date->format('Ym') . '/' . $file->id . '/' . $file->name;
            },
            'type' => 'image',
            'onCreateFile' => function ($title, $path) {
                $file = new File(['path' => $path, 'title' => $title]);
                $file->save();
                return $file;
            },
            'relation' => 'files',
            'saveFilePathInAttribute' => true,
            'rules' => [
                'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                'extensions' => ['jpg', 'jpeg', 'png'],
                'maxFiles' => 1,
                'maxSize' => 1024 * 1024 * 1, // 1 MB
                'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
            ],
            'preset' => [
                '1000x1000' => function ($realPath, $publicPath) {
                    Image::make($realPath . $publicPath)
                        ->resize(1000, 1000, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save(null, 100);
                },
            ],
            'applyPresetAfterUpload' => ['1000x1000']
        ],
    ]
];
