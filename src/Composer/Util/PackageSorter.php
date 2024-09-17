<?php











namespace Composer\Util;

use Composer\Package\PackageInterface;

class PackageSorter
{








public static function sortPackages(array $packages)
{
$usageList = array();

foreach ($packages as $package) {
foreach (array_merge($package->getRequires(), $package->getDevRequires()) as $link) {
$target = $link->getTarget();
$usageList[$target][] = $package->getName();
}
}
$computing = array();
$computed = array();
$computeImportance = function ($name) use (&$computeImportance, &$computing, &$computed, $usageList) {

if (isset($computed[$name])) {
return $computed[$name];
}


if (isset($computing[$name])) {
return 0;
}

$computing[$name] = true;
$weight = 0;

if (isset($usageList[$name])) {
foreach ($usageList[$name] as $user) {
$weight -= 1 - $computeImportance($user);
}
}

unset($computing[$name]);
$computed[$name] = $weight;

return $weight;
};

$weightList = array();

foreach ($packages as $index => $package) {
$weight = $computeImportance($package->getName());
$weightList[$index] = $weight;
}

$stable_sort = function (&$array) {
static $transform, $restore;

$i = 0;

if (!$transform) {
$transform = function (&$v, $k) use (&$i) {
$v = array($v, ++$i, $k, $v);
};

$restore = function (&$v) {
$v = $v[3];
};
}

array_walk($array, $transform);
asort($array);
array_walk($array, $restore);
};

$stable_sort($weightList);

$sortedPackages = array();

foreach (array_keys($weightList) as $index) {
$sortedPackages[] = $packages[$index];
}

return $sortedPackages;
}
}
