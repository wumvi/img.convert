<?php
declare(strict_types=1);

// php run.php --size 1000 --size 100 --jpg --svg --webp --name ddd --infile dddf.png --zip --info {ddd}

use GetOpt\GetOpt;
use GetOpt\Option;
use Symfony\Component\Filesystem\Filesystem;

include __DIR__ . '/vendor/autoload.php';

$getOpt = new GetOpt();
$optionHelp = new Option(null, 'help', GetOpt::NO_ARGUMENT);
$optionHelp->setDescription('This help');
$getOpt->addOption($optionHelp);
$option = new Option('j', 'jpg', GetOpt::NO_ARGUMENT);
$option->setDescription('Make jpg');
$getOpt->addOption($option);
$option = new Option('p', 'png', GetOpt::NO_ARGUMENT);
$option->setDescription('Make png');
$getOpt->addOption($option);
$option = new Option('w', 'webp', GetOpt::NO_ARGUMENT);
$option->setDescription('Make webp');
$getOpt->addOption($option);
$option = new Option('s', 'svg', GetOpt::NO_ARGUMENT);
$option->setDescription('Make svg');
$getOpt->addOption($option);
$option = new Option('z', 'zip', GetOpt::NO_ARGUMENT);
$option->setDescription('Make zip');
$getOpt->addOption($option);
$option = new Option('t', 'size', GetOpt::MULTIPLE_ARGUMENT);
$option->setDescription('Set size');
$getOpt->addOption($option);
$option = new Option('n', 'name', GetOpt::REQUIRED_ARGUMENT);
$option->setDescription('Set name');
$getOpt->addOption($option);
$option = new Option('i', 'infile', GetOpt::REQUIRED_ARGUMENT);
$option->setDescription('Set Infile');
$getOpt->addOption($option);
$option = new Option('d', 'info', GetOpt::REQUIRED_ARGUMENT);
$option->setDescription('Set info data');
$getOpt->addOption($option);
$option = new Option('x', 'dont-remove', GetOpt::NO_ARGUMENT);
$option->setDescription('Don\'t clear');
$getOpt->addOption($option);
$option = new Option('v', 'verbose', GetOpt::NO_ARGUMENT);
$option->setDescription('Verbose');
$getOpt->addOption($option);
$option = new Option('y', 'test-only', GetOpt::NO_ARGUMENT);
$option->setDescription('Test only');
$getOpt->addOption($option);

try {
    $getOpt->process();
} catch (\Exception $ex) {
    throw new \Exception($ex->getMessage());
}
$options = $getOpt->getOption('help');
if ($options) {
    echo $getOpt->getHelpText();
    exit;
}

$isVerbose = (bool)$getOpt->getOption('verbose');
$testOnly = (bool)$getOpt->getOption('test-only');

$name = $getOpt->getOption('name');
if (!$name) {
    throw new \Exception('Name not found. Use --name');
}

$infile = $getOpt->getOption('infile');
if (!$infile) {
    throw new \Exception('InFile not found. Use --infile');
}

$infoData = $getOpt->getOption('info');
if (!$infoData) {
    throw new \Exception('Info data not found. Use --info');
}

$sizes = $getOpt->getOption('size');
if ($sizes) {
    $sizes = array_map(function ($size) {
        return (int)$size;
    }, $sizes);
}

$rnd = 1;//mt_rand(0, 100);
$tmpDir = '/tmp/web/convert/' . $rnd . '/';
$zipFile = '/tmp/web/convert/' . $rnd . '.zip';

if ($isVerbose) {
    echo 'Tmp dir: ' . $tmpDir, PHP_EOL;
    echo 'Zip file: ' . $zipFile, PHP_EOL;
}

if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

$pngTpl = $name . '-%s.png';
foreach ($sizes as $size) {
    $pngName = sprintf($pngTpl, $size);
    $cmd = sprintf('/png.sh %s %s %s > /dev/stdout', $infile, $size, $tmpDir . $pngName);
    if ($isVerbose) {
        echo 'Cmd: ' . $cmd, PHP_EOL;
    }
    if (!$testOnly) {
        exec($cmd);
    }
}

$webpTpl = $name . '-%s.webp';
$isWebp = (bool)$getOpt->getOption('webp');
if ($isWebp) {
    foreach ($sizes as $size) {
        $pngFile = $tmpDir . sprintf($pngTpl, $size);
        $webpFile = $tmpDir . sprintf($webpTpl, $size);
        $cmd = sprintf('/webp.sh %s %s > /dev/stdout', $pngFile, $webpFile);
        if ($isVerbose) {
            echo 'Cmd: ' . $cmd, PHP_EOL;
        }
        if (!$testOnly) {
            exec($cmd);
        }
    }
}

$jpgTpl = $name . '-%s.jpg';
$isJpg = (bool)$getOpt->getOption('jpg');
if ($isJpg) {
    foreach ($sizes as $size) {
        $pngFile = $tmpDir . sprintf($pngTpl, $size);
        $jpgFile = $tmpDir . sprintf($jpgTpl, $size);
        $cmd = sprintf('/jpg.sh %s %s > /dev/stdout', $pngFile, $jpgFile);
        if ($isVerbose) {
            echo 'Cmd: ' . $cmd, PHP_EOL;
        }
        if (!$testOnly) {
            exec($cmd);
        }
    }
}

$isSvg = (bool)$getOpt->getOption('svg');
if ($isSvg) {
    $svgFile = $tmpDir . 'preview.svg';
    $cmd = sprintf('/sqip.sh %s %s > /dev/stdout', $infile, $svgFile);
    if ($isVerbose) {
        echo 'Cmd: ' . $cmd, PHP_EOL;
    }
    if (!$testOnly) {
        exec($cmd);
        $filesystem = new Filesystem();
        $filesystem->remove('/tmp/primitive_tempfile.svg');
    }
}

if ($infoData) {
    $infoFile = $tmpDir . 'info.json';
    if ($isVerbose) {
        echo 'Info file: ' . $infoFile, PHP_EOL;
        echo 'Info data: ' . $infoData, PHP_EOL;
    }
    if (!$testOnly) {
        file_put_contents($infoFile, $infoData);
    }
}

$isPng = (bool)$getOpt->getOption('png');
if (!$isPng) {
    $cmd = 'rm -rf ' . $tmpDir . '* > /dev/stdout';
    if ($isVerbose) {
        echo 'Cmd: ' . $cmd, PHP_EOL;
    }
    if (!$testOnly) {
        exec($cmd);
    }
}

$isZip = (bool)$getOpt->getOption('zip');
if ($isZip) {
    $cmd = sprintf('zip -9 -j -q %s %s > /dev/stdout', $zipFile, $tmpDir . '*');
    if ($isVerbose) {
        echo 'Cmd: ' . $cmd, PHP_EOL;
    }
    if (!$testOnly) {
        exec($cmd);
    }
}

$notClear = !(bool)$getOpt->getOption('dont-remove');
if ($notClear) {
    if ($isVerbose) {
        echo 'Remove tmp folder: ' . $tmpDir, PHP_EOL;
    }
    if (!$testOnly) {
        $filesystem->remove($tmpDir);
    }
}
