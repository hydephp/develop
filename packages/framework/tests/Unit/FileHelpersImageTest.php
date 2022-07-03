<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\Internal\FileHelpers::image
 */
class FileHelpersImageTest extends TestCase
{
   public function test_image_helper_gets_relative_web_link_to_image_stored_in_site_media_folder()
   {
       $tests = [
           'test.jpg' => 'media/test.jpg',
           'foo' => 'media/foo',
           'http://example.com/test.jpg' => 'http://example.com/test.jpg',
           'https://example.com/test.jpg' => 'https://example.com/test.jpg',
       ];

       foreach ($tests as $input => $expected) {
           $this->assertEquals(Hyde::image($input), $expected);
       }
   }

    public function test_image_helper_resolves_paths_for_nested_pages()
    {
         $tests = [
              'test.jpg' => '../media/test.jpg',
              'foo' => '../media/foo',
              'http://example.com/test.jpg' => 'http://example.com/test.jpg',
              'https://example.com/test.jpg' => 'https://example.com/test.jpg',
         ];

         foreach ($tests as $input => $expected) {
              $this->assertEquals(Hyde::image($input, 'foo/bar'), $expected);
         }
    }
}