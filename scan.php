<?php

$clusterSizes = [
  512,
  1024,
  2048,
  4096,
  8192,
  16384,
  32768,
  65536,
  131072,
  262144,
  524288,
  1048576,
  2097152
];

// Specify the directory you want to scan
$directory = 'C:\Program Files (x86)\Steam';

// Create an empty array to store file sizes
foreach ($clusterSizes as $clusterSize) {
  $fileSizes[$clusterSize] = 0;
  $totalClusters[$clusterSize] = 0;
}

// Start scanning the directory
scanDirectory($directory, $clusterSizes, $fileSizes, $totalClusters);

// Display the chart
drawChart($fileSizes, $totalClusters, $directory);


// Functions //

/**
 * The function calculates and returns half the width of a string when rendered using a specified font
 * in PHP.
 * 
 * @param string $text the text for which you want to calculate the width of.
 * @param integer $font specifies the font to be used for calculating the width of the text. 
 * The default value is 4, which corresponds to the built-in font imagefont4.
 * 
 * @return float half of the width of the image string.
 */
function halfImageStringWidth($text, $font = 4) {
  $fontWidth = imagefontwidth($font);
  $textLength = strlen($text);
  $stringWidth = $fontWidth * $textLength;
  return $stringWidth / 2;
}

/**
 * The function calculates the x-position of a string within an image based on the given x-coordinate
 * and half bar width.
 * 
 * @param int $x position of the bar on the x-axis.
 * @param int $halfBarWidth the width of a bar or object. It is
 * used to calculate the x position of the string relative to the center of the bar or object.
 * @param string value that is used to calculate the X position.
 * 
 * @return float the calculated x position.
 */
function calculateXPos($x, $halfBarWidth, $string) {
  return ($x + $halfBarWidth) - halfImageStringWidth($string);
}


/**
 * The function `drawChart` creates a bar chart image using the given file sizes and total clusters
 * data.
 * 
 * @param array $fileSizes Each element in the array represents the total size of all file if saved at a specific cluster size.
 * @param array $totalClusters Each element in the array represents the total clusters used for all file if saved at a specific cluster size..
 */
function drawChart($fileSizes, $totalClusters, $directory) {
  // Define the chart dimensions
  $width = 1600;
  $height = 900;


  // normalize the scale between 0 and 100 - same effect as dual y axes
  $fileSizesN = normalizeArray($fileSizes);
  $totalClustersN = normalizeArray($totalClusters);
  $toalBars = max(count($fileSizes), count($totalClusters));

  // Create the image canvas
  $image = imagecreatetruecolor($width, $height);

  // Define the chart colors
  $backgroundColor = imagecolorallocate($image, 255, 255, 255);
  $barColor1 = imagecolorallocate($image, 0, 128, 255);
  $barColor2 = imagecolorallocatealpha($image, 128, 0, 255, 60);
  $textColor = imagecolorallocate($image, 0, 0, 0);

  // Fill the background
  imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

  // Calculate the maximum file size and total clusters
  $maxFileSize = max($fileSizesN);
  $maxClusterCount = max($totalClustersN);

  // Define the bar width and spacing
  $barWidth = ($width - 20) / $toalBars;
  $halfBarWidth = ($barWidth / 2);

  /* The code block you provided is part of the `drawChart` function. It is responsible for drawing the
  bars and labels for the file sizes data on the chart. */
  $x = 10;
  foreach ($fileSizes as $clusterSize => $fileSize) {
    $barHeight = ($fileSizesN[$clusterSize] / $maxFileSize) * ($height - 40);
    $barLeft = $x;
    $barTop = $height - $barHeight - 20;

    imagefilledrectangle($image, $barLeft, $barTop, $barLeft + $barWidth, $height - 20, $barColor1);

    $labelY = $height - 20;
    $fileSizeFormatted = formatBytes($fileSize);
    $labelX = calculateXPos($x, $halfBarWidth, $fileSizeFormatted);
    imagestring($image, 4, $labelX, min($barTop, $height - 40), $fileSizeFormatted, $textColor);

    $x += $barWidth;
  }

  /* The code block you provided is part of the `drawChart` function. It is responsible for drawing the
  bars and labels for the total clusters data on the chart. */
  $x = 10;
  foreach ($totalClusters as $clusterSize => $totalCluster) {
    $barHeight = ($totalClustersN[$clusterSize] / $maxClusterCount) * ($height - 40);
    $barLeft = $x;
    $barTop = $height - $barHeight - 20;

    imagefilledrectangle($image, $barLeft, $barTop, $barLeft + $barWidth, $height - 20, $barColor2);


    $totalClusterFormatted = formatNums($totalCluster);
    $labelX = calculateXPos($x, $halfBarWidth, $totalClusterFormatted);
    imagestring($image, 4, $labelX, min($barTop, $height - 40), $totalClusterFormatted, $textColor);

    // Add the x-axis label
    $labelY = $height - 20;
    $labelX = calculateXPos($x, $halfBarWidth, $clusterSize);
    $labelX = ($barLeft + $halfBarWidth) - halfImageStringWidth($clusterSize);
    imagestring($image, 4, $labelX, $labelY, formatBytes($clusterSize), $textColor);


    // increment X
    $x += $barWidth;
  }


  // Add some labels
  imagestring(
    $image,
    4,
    10,
    1,
    'Total Clusters',
    $textColor
  );

  imagestring(
    $image,
    4,
    $width - 100,
    1,
    'Total Space',
    $textColor
  );

  $halfDirectorySize = halfImageStringWidth($directory);


  imagestring(
    $image,
    4,
    ($width / 2) - $halfDirectorySize,
    1,
    $directory,
    $textColor
  );

  // Output the chart as a PNG image
  header('Content-Type: image/png');
  imagepng($image);
  imagedestroy($image);
}

/**
 * The function "scanDirectory" recursively scans a directory and calculates the total file sizes and
 * number of clusters for each specified cluster size.
 * 
 * @param string $dir the directory that you want to scan. It should be a string
 * representing the path to the directory on the file system.
 * @param array $clusterSizes is an array that contains the sizes of clusters in
 * bytes. It is used to calculate the file size and the number of clusters occupied by each file in the
 * directory.
 * @param array $fileSizes an array that will store the total size of files in
 * each cluster size. The keys of the array represent the cluster sizes, and the values represent the
 * total size of files in that cluster size.
 * @param array $totalClusters an array that keeps track of the total number
 * of clusters used by files of different cluster sizes. The keys of the array represent the cluster
 * sizes, and the values represent the total number of clusters used by files of that cluster size.
 * 
 * @return void The function does not explicitly return a value. It echoes an error message if the directory
 * is invalid, but it does not return any specific value.
 */
function scanDirectory($dir, $clusterSizes, &$fileSizes, &$totalClusters) {
  if (!is_dir($dir)) {
    echo "Invalid directory: $dir";
    return;
  }

  $files = scandir($dir);
  foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
      continue;
    }

    $path = $dir . DIRECTORY_SEPARATOR . $file;
    if (is_dir($path)) {
      scanDirectory($path, $clusterSizes, $fileSizes, $totalClusters);
    } else {
      $size = filesize($path);
      foreach ($clusterSizes as $clusterSize) {
        $clusters = ceil($size / $clusterSize);
        $fileSize = $clusters * $clusterSize;
        $fileSizes[$clusterSize] += $fileSize;
        $totalClusters[$clusterSize] += $clusters;
      }
    }
  }
}



/**
 * The function formatBytes converts a given number of bytes into a human-readable format with
 * appropriate units (B, KB, MB, GB, TB).
 * 
 * @param integer bytes The parameter `` represents the number of bytes that you want to format.
 * 
 * @return string a formatted string that represents the given number of bytes in a human-readable format. The
 * string includes the number of bytes rounded to two decimal places and the appropriate unit (B, KB,
 * MB, GB, or TB) based on the size of the input.
 */
function formatBytes($bytes) {
  $units = array('B', 'KB', 'MB', 'GB', 'TB');
  $unitIndex = 0;

  while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
    $bytes /= 1024;
    $unitIndex++;
  }

  return round($bytes, 2) . ' ' . $units[$unitIndex];
}

/**
 * The function "formatNums" takes a number as input and returns a formatted string with the number
 * rounded to two decimal places and a unit suffix (K, Mn, Bn, Tn) based on the magnitude of the
 * number.
 * 
 * @param integer number The number parameter is the number that you want to format.
 * 
 * @return string a formatted number with a unit suffix.
 */
function formatNums($number) {
  $units = array('', 'K', 'Mn', 'Bn', 'Tn');
  $unitIndex = 0;

  while ($number >= 1024 && $unitIndex < count($units) - 1) {
    $number /= 1024;
    $unitIndex++;
  }

  return round($number, 2) . ' ' . $units[$unitIndex];
}

/**
 * The normalizeArray function takes an array of numbers and returns a new array where each number is
 * normalized to a range between 1 and 100.
 * 
 * @param array The input array that you want to normalize.
 * 
 * @return array a normalized array.
 */
function normalizeArray($array) {
  $minValue = min($array);
  $maxValue = max($array);
  $normalizedArray = [];

  foreach ($array as $key => $value) {
    $normalizedValue = ($value - $minValue) / ($maxValue - $minValue) * 99 + 1;
    $normalizedArray[$key] = $normalizedValue;
  }

  return $normalizedArray;
}
