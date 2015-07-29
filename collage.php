<?php

class Collage
{
    protected $images;
    protected $width;
    protected $height;
    protected $grid_width;
    protected $grid_height;
    protected $image;

    public function __construct($images, $width = 500, $height = 500, $grid_width = 10, $grid_height = 10)
    {
        $this->images = $images;
        $this->width = $width;
        $this->height = $height;
        $this->grid_width = $grid_width;
        $this->grid_height = $grid_height;

        $this->image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($this->image, 255, 255, 255);
        imagefill($this->image, 0, 0, $white);
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function display()
    {
        $this->grid();
        $this->box(3, 4, 2, 5);
        foreach ($this->images as $image) {
            $img = imagecreatefromjpeg($image);
            $this->image_box($img, 3, 4, 2, 5);
            break;
        }
        header("Content-type: image/jpeg");
        imagejpeg($this->image);
    }

    private function grid()
    {
        $black = imagecolorallocate($this->image, 0, 0, 0);
        imagesetthickness($this->image, 3);
        $cell_width = ($this->width - 1) / $this->grid_width;   // note: -1 to avoid writting
        $cell_height = ($this->height - 1) / $this->grid_height; // a pixel outside the image
        for ($x = 0; $x <= $this->grid_width; $x++) {
            for ($y = 0; $y <= $this->grid_height; $y++) {
                imageline($this->image, ($x * $cell_width), 0, ($x * $cell_width), $this->height, $black);
                imageline($this->image, 0, ($y * $cell_height), $this->width, ($y * $cell_height), $black);
            }
        }
    }

    private function box($w, $h, $x, $y)
    {
        // Cell width
        $cell_width = $this->width / $this->grid_width;
        $cell_height = $this->height / $this->grid_height;

        // Conversion of our virtual sizes/positions to real ones
        $size_w = ($cell_width * $w);
        $size_h = ($cell_height * $h);
        $pos_x = ($cell_width * $x);
        $pos_y = ($cell_height * $y);

        // Getting top left and bottom right of our rectangle
        $top_left_x = $pos_x;
        $top_left_y = $pos_y;
        $bottom_right_x = $pos_x + $size_w;
        $bottom_right_y = $pos_y + $size_h;

        // Displaying rectangle
        $red = imagecolorallocate($this->image, 100, 0, 0);
        imagefilledrectangle($this->image, $top_left_x, $top_left_y, $bottom_right_x, $bottom_right_y, $red);
    }

    public function image_box($img, $w, $h, $x, $y)
    {
        // Cell width
        $cell_width = $this->width / $this->grid_width;
        $cell_height = $this->height / $this->grid_height;

        // Conversion of our virtual sizes/positions to real ones
        $size_w = ceil($cell_width * $w);
        $size_h = ceil($cell_height * $h);
        $pos_x = ($cell_width * $x);
        $pos_y = ($cell_height * $y);

        // Copying the image
        imagecopyresampled($this->image, $img, $pos_x, $pos_y, 0, 0, $size_w, $size_h, imagesx($img), imagesy($img));
    }

}

$images = [];
$path = '5';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $images[] = $path . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
    }
}
$collage = new Collage($images);
$collage->display();
?>
