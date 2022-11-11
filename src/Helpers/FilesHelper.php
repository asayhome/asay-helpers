<?php

namespace AsayHome\AsayHelpers\Helpers;

use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class FilesHelper
{


    public static function checkFileExtension($file, $types = [])
    {
        $mimeType = $file->getMimeType();
        $extension = explode('/', $mimeType)[1];
        if (in_array($extension, $types)) {
            return (object)[
                'success' => true,
                'msg' => __('Accepted')
            ];
        }
        return (object)[
            'success' => false,
            'msg' => __('This type of files not allowed'),
            'extension' => $extension,

        ];
    }
    public static function checkFileSize($file, $maxSize)
    {
        $file_size = $file->getSize() / 1024 / 1024;
        if ($file_size <= $maxSize) {
            return (object)[
                'success' => true,
                'msg' => __('Accepted')
            ];
        }
        return (object)[
            'success' => false,
            'msg' => __('file size must be less than or equals') . ' ' . $maxSize,
            'file_size' => $file_size
        ];
    }

    public static function getAllowedFilesExtensions()
    {
        $settings = AppHelper::getSettings([
            'jpg_file',
            'jpeg_file',
            'png_file',
            'pdf_file',
        ]);
        $extension = [];
        if ($settings['jpg_file']) {
            array_push($extension, 'jpg');
        }
        if ($settings['jpeg_file']) {
            array_push($extension, 'jpeg');
        }
        if ($settings['png_file']) {
            array_push($extension, 'png');
        }
        if ($settings['pdf_file']) {
            array_push($extension, 'pdf');
        }

        return $extension;
    }

    public static function getAllowedImagesExtensions()
    {
        return ['png', 'jpg', 'jpeg'];
    }

    public static function storeFile($file, $storedLocation)
    {
        // $settings = AppHelper::getSettings([
        //     'imageprocessapi_key',
        // ]);
        $path = $file->store($storedLocation, 'public');

        $mimeType = $file->getMimeType();

        if (in_array($mimeType, self::getAllowedImagesExtensions())) {
            ImageOptimizer::optimize(storage_path('app/public/' . $path));
        }

        return $path;
    }
}
