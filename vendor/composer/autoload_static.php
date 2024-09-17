<?php



namespace Composer\Autoload;

class ComposerStaticInitComposerPhar1636470124
{
public static $files = array (
'320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
'0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
'7844cce90d4037f1a077c08319eef109' => __DIR__ . '/..' . '/react/promise/src/React/Promise/functions_include.php',
);

public static $prefixLengthsPsr4 = array (
'S' => 
array (
'Symfony\\Polyfill\\Mbstring\\' => 26,
'Symfony\\Polyfill\\Ctype\\' => 23,
'Symfony\\Component\\Process\\' => 26,
'Symfony\\Component\\Finder\\' => 25,
'Symfony\\Component\\Filesystem\\' => 29,
'Symfony\\Component\\Debug\\' => 24,
'Symfony\\Component\\Console\\' => 26,
'Seld\\PharUtils\\' => 15,
'Seld\\JsonLint\\' => 14,
),
'P' => 
array (
'Psr\\Log\\' => 8,
),
'J' => 
array (
'JsonSchema\\' => 11,
),
'C' => 
array (
'Composer\\XdebugHandler\\' => 23,
'Composer\\Spdx\\' => 14,
'Composer\\Semver\\' => 16,
'Composer\\MetadataMinifier\\' => 26,
'Composer\\CaBundle\\' => 18,
'Composer\\' => 9,
),
);

public static $prefixDirsPsr4 = array (
'Symfony\\Polyfill\\Mbstring\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
),
'Symfony\\Polyfill\\Ctype\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
),
'Symfony\\Component\\Process\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/process',
),
'Symfony\\Component\\Finder\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/finder',
),
'Symfony\\Component\\Filesystem\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/filesystem',
),
'Symfony\\Component\\Debug\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/debug',
),
'Symfony\\Component\\Console\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/console',
),
'Seld\\PharUtils\\' => 
array (
0 => __DIR__ . '/..' . '/seld/phar-utils/src',
),
'Seld\\JsonLint\\' => 
array (
0 => __DIR__ . '/..' . '/seld/jsonlint/src/Seld/JsonLint',
),
'Psr\\Log\\' => 
array (
0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
),
'JsonSchema\\' => 
array (
0 => __DIR__ . '/..' . '/justinrainbow/json-schema/src/JsonSchema',
),
'Composer\\XdebugHandler\\' => 
array (
0 => __DIR__ . '/..' . '/composer/xdebug-handler/src',
),
'Composer\\Spdx\\' => 
array (
0 => __DIR__ . '/..' . '/composer/spdx-licenses/src',
),
'Composer\\Semver\\' => 
array (
0 => __DIR__ . '/..' . '/composer/semver/src',
),
'Composer\\MetadataMinifier\\' => 
array (
0 => __DIR__ . '/..' . '/composer/metadata-minifier/src',
),
'Composer\\CaBundle\\' => 
array (
0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
),
'Composer\\' => 
array (
0 => __DIR__ . '/../..' . '/src/Composer',
),
);

public static $prefixesPsr0 = array (
'R' => 
array (
'React\\Promise' => 
array (
0 => __DIR__ . '/..' . '/react/promise/src',
),
),
);

public static $classMap = array (
'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
);

public static function getInitializer(ClassLoader $loader)
{
return \Closure::bind(function () use ($loader) {
$loader->prefixLengthsPsr4 = ComposerStaticInitComposerPhar1636470124::$prefixLengthsPsr4;
$loader->prefixDirsPsr4 = ComposerStaticInitComposerPhar1636470124::$prefixDirsPsr4;
$loader->prefixesPsr0 = ComposerStaticInitComposerPhar1636470124::$prefixesPsr0;
$loader->classMap = ComposerStaticInitComposerPhar1636470124::$classMap;

}, null, ClassLoader::class);
}
}
