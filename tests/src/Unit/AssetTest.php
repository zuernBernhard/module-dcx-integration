<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\Tests\dcx_integration\Unit;

use Drupal\dcx_integration\Asset\Image;
use Drupal\dcx_integration\Asset\Article;
use Drupal\Tests\UnitTestCase;

/**
 * Description of AssetTest
 *
 * @author go
 */
class AssetTest extends UnitTestCase {

  function testCreateImage__mandatory_attr() {
    $data = [];
    foreach (Image::$mandatory_attributes as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    $asset = new Image($data);

    $this->assertArrayEquals($data, $asset->data(), "Mandatory attributes suffice to create an Image");
  }

  function testCreateImage__optional_attr() {
    $data = [];
    foreach (array_merge(Image::$mandatory_attributes, Image::$optional_attributes) as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    $asset = new Image($data);

    $this->assertArrayEquals($data, $asset->data(), "Mandatory and optional attributes are able to create an Image");
  }

  function testCreateImage__missing_mandatory() {
    $data = [];
    foreach (Image::$mandatory_attributes as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    array_shift($data);

    $this->setExpectedException('Drupal\dcx_integration\Exception\MandatoryAttributeException');
    $asset = new Image($data);
  }

  function testCreateImage__stray_option() {
    $invalid_attribute = 'invalid';
    $this->assertTrue(!in_array($invalid_attribute, Image::$mandatory_attributes + Image::$optional_attributes), 'Invalid attribute in the list of mandatory or optional attributes.');

    $data = [];
    foreach (array_merge(Image::$mandatory_attributes, [$invalid_attribute])  as $attr) {
      $data[$attr] = 'test__' . $attr;
    }

    $this->setExpectedException('Drupal\dcx_integration\Exception\IllegalAttributeException');
    $asset = new Image($data);
  }

  function testCreateArticle__mandatory_attr() {
    $data = [];
    foreach (Article::$mandatory_attributes as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    $asset = new Article($data);

    $this->assertArrayEquals($data, $asset->data(), "Mandatory attributes suffice to create an Article");
  }

  function testCreateArticle__optional_attr() {
    $data = [];
    foreach (array_merge(Article::$mandatory_attributes, Article::$optional_attributes) as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    $asset = new Article($data);

    $this->assertArrayEquals($data, $asset->data(), "Mandatory and optional attributes are able to create an Article");
  }

  function testCreateArticle__missing_mandatory() {
    $data = [];
    foreach (Article::$mandatory_attributes as $attr) {
      $data[$attr] = 'test__' . $attr;
    }
    array_shift($data);

    $this->setExpectedException('Drupal\dcx_integration\Exception\MandatoryAttributeException');
    $asset = new Article($data);
  }

  function testCreateArticle__stray_option() {
    $invalid_attribute = 'invalid';
    $this->assertTrue(!in_array($invalid_attribute, Article::$mandatory_attributes + Article::$optional_attributes), 'Invalid attribute in the list of mandatory or optional attributes.');

    $data = [];
    foreach (array_merge(Article::$mandatory_attributes, [$invalid_attribute])  as $attr) {
      $data[$attr] = 'test__' . $attr;
    }

    $this->setExpectedException('Drupal\dcx_integration\Exception\IllegalAttributeException');
    $asset = new Article($data);
  }
}
