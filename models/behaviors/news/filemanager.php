<?php

use Intervention\Image\ImageManagerStatic as Image;
use yii\helpers\ArrayHelper;
use app\models\File;

return [
    'class' => 'rkit\filemanager\behaviors\FileBehavior',
    'attributes' => [
        'preview' => [
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
            'extraFields' => function () {
                return [
                    'type' => 1,
                ];
            },
            'relation' => 'files',
            'relationQuery' => function ($query) {
                return $query->andWhere(['type' => 1]);
            },
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
                '200x200' => function ($realPath, $publicPath, $thumbPath) {
                    Image::make($realPath . $publicPath)
                        ->fit(200, 200)
                        ->save($realPath . $thumbPath, 100);
                },
                '1000x1000' => function ($realPath, $publicPath, $thumbPath) {
                    Image::make($realPath . $publicPath)
                        ->resize(1000, 1000, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save(null, 100);
                },
            ],
            'applyPresetAfterUpload' => '*',
        ],
        'gallery' => [
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
            'onUpdateFile' => function ($file) {
                if (is_array($this->galleryTitles)) {
                    $file->title = ArrayHelper::getValue($this->galleryTitles, $file->id, $file->title);
                }
                return $file;
            },
            'extraFields' => function ($model, $attribute, $file, $fields) {
                $position = 0;
                if (is_array($this->galleryTitles)) {
                    $position = array_search($file->id, array_keys($this->galleryTitles));
                    $position++;
                }
                return [
                    'type' => 2,
                    'position' => $position,
                ];
            },
            'relation' => 'files',
            'relationQuery' => function ($query) {
                return $query->andWhere(['type' => 2]);
            },
            'multiple' => true,
            'template' => '@app/modules/admin/views/shared/files/gallery/item.php',
            'rules' => [
                'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                'extensions' => ['jpg', 'jpeg', 'png'],
                'maxSize' => 1024 * 1024 * 1, // 1 MB
                'maxFiles' => 10,
                'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
            ],
            'preset' => [
                '80x80' => function ($realPath, $publicPath, $thumbPath) {
                    Image::make($realPath . $publicPath)
                        ->fit(80, 80)
                        ->save($realPath . $thumbPath, 100);
                },
            ],
        ],
        'text' => [
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
            'extraFields' => function () {
                return [
                    'type' => 3,
                ];
            },
            'relation' => 'files',
            'relationQuery' => function ($query) {
                return $query->andWhere(['type' => 3]);
            },
            'rules' => [
                'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                'extensions' => ['jpg', 'jpeg', 'png'],
                'maxSize' => 1024 * 1024 * 1, // 1 MB
                'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
            ]
        ],
    ]
];
