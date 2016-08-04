<?php

/**
 * @file
 * Contains \Drupal\facebook_image_effect\Plugin\ImageEffect\FacebookImageEffect.
 */

namespace Drupal\facebook_image_effect\Plugin\ImageEffect;

use Drupal\Core\Image\ImageInterface;
use Drupal\image\ImageEffectBase;

/**
 * Provides a 'FacebookImageEffect' image effect.
 *
 * @ImageEffect(
 *  id = "fb_1200x627",
 *  label = @Translation("Facebook 1200x627"),
 *  description = @Translation("Resizes to 1200/627 by extending it with a flipped version of the image.")
 * )
 */
class FacebookImageEffect extends ImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    $H = 627;
    $W = 1200;

    $h = $image->getHeight();
    $w = $image->getWidth();

    // If the original has an aspect ratio even wider than 1200x627 we just
    // crop it.
    if ($w/$h > $W/$H) {
      return $image->scaleAndCrop($W, $H);
    }

    $src_image = $image->getToolkit()->getResource();

    // Create image with the target size and set as resource
    $dst_image = imagecreatetruecolor($W, $H);
    $image->getToolkit()->setResource($dst_image);

    // This makes sure we end up with a even number of pixels.
    $resizedW = round($H/$h*$w/2)*2;

    $dst_x = ($W-$resizedW)/2;

    $dst_y = 0;
    $dst_w = $resizedW;
    $dst_h = $H;

    $src_x = 0;
    $src_y = 0;
    $src_w = $w;
    $src_h = $h;

    // Put scaled version in the center of new image
    $result = imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    // Prepare flipped scaled version of original
    imageflip($src_image, IMG_FLIP_HORIZONTAL);
    $scale_image = imagecreatetruecolor($resizedW,$H);
    imagecopyresampled($scale_image, $src_image, 0,0 , 0,0, $resizedW,$H, $w,$h);
    imagedestroy($src_image);

    // Fit the flipped scaled version to the edges of the middle image
    imagecopy($dst_image, $scale_image, $dst_x + $resizedW,0 , 0,0 , $resizedW,$H);
    imagecopy($dst_image, $scale_image, $dst_x - $resizedW,0 , 0,0 , $resizedW,$H);

    // This is for the case, where the aspect of the source image is below
    // 400/627 which makes us to ammend the image zwo time
    if ($resizedW * 3 < $W) {
      imageflip($scale_image, IMG_FLIP_HORIZONTAL);
      imagecopy($dst_image, $scale_image, $dst_x + 2 * $resizedW,0 , 0,0 , $resizedW,$H);
      imagecopy($dst_image, $scale_image, $dst_x - 2 * $resizedW,0 , 0,0 , $resizedW,$H);
    }

    imagedestroy($scale_image);

    return $result;
  }

}
