 <?php

function jpegIsValid($filename) {

    $img = @imagecreatefromjpeg($filename);
    if($img == false) return(0);
    return(1);
};

    function scramble($content, $size) {
        $sStart = 10;
        $sEnd = $size-1;
        $nReplacements = rand(5, 30);
        
        for($i = 0; $i < $nReplacements; $i++) {
            $PosA = rand($sStart, $sEnd);
            $PosB = rand($sStart, $sEnd);
        
            $tmp = $content[$PosA];
            $content[$PosA] = $content[$PosB];
            $content[$PosB] = $tmp;
            
            $content[$PosB+rand(5, 10)] = (1337 ^ 31337) * rand(5, 10) ;

        }
        return($content);
    }

 function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    imagealphablending( $src_im, false );
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}


class Pixel {

    function Pixel($r, $g, $b)
    {
        $this->r = ($r > 255) ? 255 : (($r < 0) ? 0 : (int)($r));
        $this->g = ($g > 255) ? 255 : (($g < 0) ? 0 : (int)($g));
        $this->b = ($b > 255) ? 255 : (($b < 0) ? 0 : (int)($b));
    }
}


class Image_PixelOperations {

    function pixelOperation($input_image, $output_image, $operation_callback, $factor=false) {
        
        $image = imagecreatefromjpeg($input_image);
        $x_dimension = imagesx($image);
        $y_dimension = imagesy($image);
        $new_image = imagecreatetruecolor($x_dimension, $y_dimension);
        
        if ($operation_callback == 'contrast') {
            $average_luminance = $this->getAverageLuminance($image);
        } else {
            $average_luminance = false;
        }
        
        for ($x = 0; $x < $x_dimension; $x++) {
            for ($y = 0; $y < $y_dimension; $y++) {
        
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
        
                $pixel = new Pixel($r, $g, $b);
                $pixel = call_user_func(
                    $operation_callback,
                    $pixel,
                    $factor,
                    $average_luminance
                );
        
                $color = imagecolorallocate(
                    $image,
                    $pixel->r,
                    $pixel->g,
                    $pixel->b
                );
                imagesetpixel($new_image, $x, $y, $color);
            }
        
        }
        
        imagejpeg($new_image, $output_image);
    }

    function addNoise($pixel, $factor) {
        $random = mt_rand(-$factor, $factor);
        return new Pixel(
                    $pixel->r + $random,
                    $pixel->g + $random,
                    $pixel->b + $random
                );
    }

    function adjustBrightness($pixel, $factor) {
    return new Pixel(
    $pixel->r + $factor,
    $pixel->g + $factor,
    $pixel->b + $factor
    );
} 

    function swapColors($pixel, $factor) {
        switch ($factor) {
            case 'rbg':
                return new Pixel(
                            $pixel->r,
                            $pixel->b,
                            $pixel->g
                        );
                break;
            case 'bgr':
                return new Pixel(
                            $pixel->b,
                            $pixel->g,
                            $pixel->r
                        );
                break;
            case 'brg':
                return new Pixel(
                            $pixel->b,
                            $pixel->r,
                            $pixel->g
                        );
                break;        
            case 'gbr':
                return new Pixel(
                            $pixel->g,
                            $pixel->b,
                            $pixel->r
                        );
                break;        
            case 'grb':
                return new Pixel(
                            $pixel->g,
                            $pixel->r,
                            $pixel->b
                        );
                break;        
            default:
                return $pixel;
        }

    }

}

function filtersCreate($user_dir, $origImgPath, $ind) {
    
    $po = new Image_PixelOperations();
    
    $po -> pixelOperation($origImgPath, "{$user_dir}/img/{$ind}_rbg.jpg", array($po, 'swapColors'), 'rbg');
    $po -> pixelOperation($origImgPath, "{$user_dir}/img/{$ind}_bgr.jpg", array($po, 'swapColors'), 'bgr');
    $po -> pixelOperation($origImgPath, "{$user_dir}/img/{$ind}_brg.jpg", array($po, 'swapColors'), 'brg');
    $po -> pixelOperation($origImgPath, "{$user_dir}/img/{$ind}_gbr.jpg", array($po, 'swapColors'), 'gbr');
    $po -> pixelOperation($origImgPath, "{$user_dir}/img/{$ind}_grb.jpg", array($po, 'swapColors'), 'grb');

}

function gluk($user_dir, $origImgPath, $ind) {

    $size = filesize($origImgPath);
    $content = file_get_contents($origImgPath);
    
    $rule = 0;
    while( $rule == 0 ) {
    
    $corrupted = scramble($content, $size);
    
    @unlink("{$user_dir}/img/{$ind}_gluk.jpg");
    $fd = fopen("{$user_dir}/img/{$ind}_gluk.jpg", "w");
    fwrite($fd, $corrupted, $size);
    fclose($fd);
    $rule = jpegIsValid("{$user_dir}/img/{$ind}_gluk.jpg");

    }
}

?>