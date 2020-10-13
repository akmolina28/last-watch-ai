<?php

namespace App;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DetectionMask
{
    protected $png_file_path;

    public function __construct($png_file_name) {
        $this->png_file_path = Storage::path('masks/'.$png_file_name);
    }

    public function is_object_outside_mask($x_min, $x_max, $y_min, $y_max) {
        $detectionPoints = [
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
        ];

        $k = 0;

        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $x = $x_min + ($x_max - $x_min) * $i * 0.25;
                $y = $y_min + ($y_max - $y_min) * $j * 0.25;

                $detectionPoints[$k] = [$x, $y];
                $k++;
            }
        }

        $im = $im = imagecreatefrompng($this->png_file_path);
        $outsideMaskCount = 0;
        $outsideMaskThreshold = 5;
        for ($i = 0; $i < 9; $i++) {
            $x = $detectionPoints[$i][0];
            $y = $detectionPoints[$i][1];
            $rgba = imagecolorat($im,$x,$y);
            $alpha = ($rgba & 0x7F000000) >> 24;

            // 0 is opaque, 127 is transparent
            if ($alpha > 117) {
                $outsideMaskCount++;
                if ($outsideMaskCount >= $outsideMaskThreshold) {
                    return true;
                }
            }
        }

        return false;
    }
}
