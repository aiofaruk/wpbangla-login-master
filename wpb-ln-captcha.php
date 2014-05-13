<?php
/*
 WPB Login Master
 (c) 2014. Shameem Reza
 http://shameemreza.info
*/

define('wpb_login_master_MAX_CAPTCHA', 10);

class wpb_ln_captcha {
  // convert HEX(HTML) color notation to RGB
  static function hex2rgb($color) {
    if ($color[0] == '#') {
      $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    } else {
        return array(255, 255, 255);
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array($r, $g, $b);
  } // html2rgb


  // output captcha image
  static function generate() {
    $a = rand(0, (int) wpb_login_master_MAX_CAPTCHA);
    $b = rand(0, (int) wpb_login_master_MAX_CAPTCHA);
    $color = @$_GET['color'];
    $color = urldecode($color);

    if ($a > $b) {
      $out = "$a - $b";
      $_SESSION['captcha'] = $a - $b;
    } else {
      $out = "$a + $b";
      $_SESSION['captcha'] = $a + $b;
    }

    $font   = 5;
    $width  = ImageFontWidth($font) * strlen($out);
    $height = ImageFontHeight($font);
    $im     = ImageCreate($width, $height);

    $x = imagesx($im) - $width ;
    $y = imagesy($im) - $height;

    $white = imagecolorallocate ($im, 255, 255, 255);
    $gray = imagecolorallocate ($im, 66, 66, 66);
    $black = imagecolorallocate ($im, 0, 0, 0);
    $trans_color = $white; //transparent colour
    
    if ($color) {
      $color = self::hex2rgb($color);
      $new_color = imagecolorallocate ($im, $color[0], $color[1], $color[2]);
      imagefill($im, 1, 1, $new_color);
    } else {
      imagecolortransparent($im, $trans_color);
    }

    imagestring ($im, $font, $x, $y, $out, $black);

    if (@$_GET['noise']) {
      //mess up the image a bit
      $style = array($black, $white, $white, $white, $black);
      imagesetstyle($im, $style);
      imageline($im, rand(0, $width), 0, rand(0, $width), $height, IMG_COLOR_STYLED);
      imageline($im, rand(0, $width), 0, rand(0, $width), $height, IMG_COLOR_STYLED);
      imageline($im, rand(0, $width), 0, rand(0, $width), $height, IMG_COLOR_STYLED);
    }

    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: image/gif');
    imagegif($im);
    die();
  } // create


  static function get() {
    return $_SESSION['captcha'];
  } // get
} // wpb_ln_captcha


if (isset($_GET['wpb-generate-image'])) {
  @session_start();
  wpb_ln_captcha::generate();
}
?>