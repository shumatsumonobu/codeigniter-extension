<?php
use PHPUnit\Framework\TestCase;
use \X\Util\ImageHelper;
use \X\Util\FileHelper;

final class ImageHelperTest extends TestCase {
  const TMPDIR = __DIR__ . '/tmp';
  const INDIR = __DIR__ . '/input';
  const OUTDIR = __DIR__ . '/output';

  public static function setUpBeforeClass(): void {
    // During testing, files in the input directory are overwritten, so reset the input directory before testing.
    FileHelper::delete(self::TMPDIR);
    FileHelper::copyDirectory(self::INDIR, self::TMPDIR);
  }

  public function testWriteFirstFrameOfGifInASeparateFile(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/animated.gif';
    $dest = self::OUTDIR .  '/first-frame-of-gif.gif';
    ImageHelper::extractFirstFrameOfGif($src, $dest);
    $this->assertSame(ImageHelper::getNumberOfGifFrames($dest), 1);
  }

  public function testWriteFirstFrameOfGifInSameFile(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/animated.gif';
    ImageHelper::extractFirstFrameOfGif($src);
    $this->assertSame(ImageHelper::getNumberOfGifFrames($src), 1);
  }

  public function testGetNumberOfFramesInGif(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/animated2.gif';
    $this->assertSame(ImageHelper::getNumberOfGifFrames($src), 19);
  }

  public function testGetNumberOfFramesOfAGifWithoutAnimation(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/non-animated.gif';
    $this->assertSame(ImageHelper::getNumberOfGifFrames($src), 1);
  }

  public function testWriteAllPagesOfPdfAsImage(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/sample.pdf';
    $dest = self::OUTDIR .  '/pdf.jpg';
    ImageHelper::pdf2Image($src, $dest);
    $this->assertSame(true, true);
  }

  public function testWriteOnlyFirstPageOfPdfAsmage(): void {
    if (!class_exists('Imagick'))
      $this->markTestSkipped('Imagick extension is not installed.');
    $src = self::TMPDIR . '/sample.pdf';
    $dest = self::OUTDIR .  '/pdf.jpg';
    ImageHelper::pdf2Image($src, $dest, ['pageNumber' => 0]);
    $this->assertSame(true, true);
  }
}
