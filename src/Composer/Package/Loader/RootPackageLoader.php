<?php











namespace Composer\Package\Loader;

use Composer\Package\BasePackage;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\RootAliasPackage;
use Composer\Repository\RepositoryFactory;
use Composer\Package\Version\VersionGuesser;
use Composer\Package\Version\VersionParser;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use Composer\Util\ProcessExecutor;








class RootPackageLoader extends ArrayLoader
{



private $manager;




private $config;




private $versionGuesser;

public function __construct(RepositoryManager $manager, Config $config, VersionParser $parser = null, VersionGuesser $versionGuesser = null, IOInterface $io = null)
{
parent::__construct($parser);

$this->manager = $manager;
$this->config = $config;
$this->versionGuesser = $versionGuesser ?: new VersionGuesser($config, new ProcessExecutor($io), $this->versionParser);
}












public function load(array $config, $class = 'Composer\Package\RootPackage', $cwd = null)
{
if ($class !== 'Composer\Package\RootPackage') {
trigger_error('The $class arg is deprecated, please reach out to Composer maintainers ASAP if you still need this.', E_USER_DEPRECATED);
}

if (!isset($config['name'])) {
$config['name'] = '__root__';
} elseif ($err = ValidatingArrayLoader::hasPackageNamingError($config['name'])) {
throw new \RuntimeException('Your package name '.$err);
}
$autoVersioned = false;
if (!isset($config['version'])) {
$commit = null;


if (getenv('COMPOSER_ROOT_VERSION')) {
$config['version'] = getenv('COMPOSER_ROOT_VERSION');
} else {
$versionData = $this->versionGuesser->guessVersion($config, $cwd ?: getcwd());
if ($versionData) {
$config['version'] = $versionData['pretty_version'];
$config['version_normalized'] = $versionData['version'];
$commit = $versionData['commit'];
}
}

if (!isset($config['version'])) {
$config['version'] = '1.0.0';
$autoVersioned = true;
}

if ($commit) {
$config['source'] = array(
'type' => '',
'url' => '',
'reference' => $commit,
);
$config['dist'] = array(
'type' => '',
'url' => '',
'reference' => $commit,
);
}
}


$package = parent::load($config, $class);
if ($package instanceof RootAliasPackage) {
$realPackage = $package->getAliasOf();
} else {
$realPackage = $package;
}

if (!$realPackage instanceof RootPackage) {
throw new \LogicException('Expecting a Composer\Package\RootPackage at this point');
}

if ($autoVersioned) {
$realPackage->replaceVersion($realPackage->getVersion(), RootPackage::DEFAULT_PRETTY_VERSION);
}

if (isset($config['minimum-stability'])) {
$realPackage->setMinimumStability(VersionParser::normalizeStability($config['minimum-stability']));
}

$aliases = array();
$stabilityFlags = array();
$references = array();
foreach (array('require', 'require-dev') as $linkType) {
if (isset($config[$linkType])) {
$linkInfo = BasePackage::$supportedLinkTypes[$linkType];
$method = 'get'.ucfirst($linkInfo['method']);
$links = array();
foreach ($realPackage->$method() as $link) {
$links[$link->getTarget()] = $link->getConstraint()->getPrettyString();
}
$aliases = $this->extractAliases($links, $aliases);
$stabilityFlags = self::extractStabilityFlags($links, $realPackage->getMinimumStability(), $stabilityFlags);
$references = self::extractReferences($links, $references);

if (isset($links[$config['name']])) {
throw new \RuntimeException(sprintf('Root package \'%s\' cannot require itself in its composer.json' . PHP_EOL .
'Did you accidentally name your root package after an external package?', $config['name']));
}
}
}

foreach (array_keys(BasePackage::$supportedLinkTypes) as $linkType) {
if (isset($config[$linkType])) {
foreach ($config[$linkType] as $linkName => $constraint) {
if ($err = ValidatingArrayLoader::hasPackageNamingError($linkName, true)) {
throw new \RuntimeException($linkType.'.'.$err);
}
}
}
}

$realPackage->setAliases($aliases);
$realPackage->setStabilityFlags($stabilityFlags);
$realPackage->setReferences($references);

if (isset($config['prefer-stable'])) {
$realPackage->setPreferStable((bool) $config['prefer-stable']);
}

if (isset($config['config'])) {
$realPackage->setConfig($config['config']);
}

$repos = RepositoryFactory::defaultRepos(null, $this->config, $this->manager);
foreach ($repos as $repo) {
$this->manager->addRepository($repo);
}
$realPackage->setRepositories($this->config->getRepositories());

return $package;
}







private function extractAliases(array $requires, array $aliases)
{
foreach ($requires as $reqName => $reqVersion) {
if (preg_match('{^([^,\s#]+)(?:#[^ ]+)? +as +([^,\s]+)$}', $reqVersion, $match)) {
$aliases[] = array(
'package' => strtolower($reqName),
'version' => $this->versionParser->normalize($match[1], $reqVersion),
'alias' => $match[2],
'alias_normalized' => $this->versionParser->normalize($match[2], $reqVersion),
);
} elseif (strpos($reqVersion, ' as ') !== false) {
throw new \UnexpectedValueException('Invalid alias definition in "'.$reqName.'": "'.$reqVersion.'". Aliases should be in the form "exact-version as other-exact-version".');
}
}

return $aliases;
}













public static function extractStabilityFlags(array $requires, $minimumStability, array $stabilityFlags)
{
$stabilities = BasePackage::$stabilities;

$minimumStability = $stabilities[$minimumStability];
foreach ($requires as $reqName => $reqVersion) {
$constraints = array();


$orSplit = preg_split('{\s*\|\|?\s*}', trim($reqVersion));
foreach ($orSplit as $orConstraint) {
$andSplit = preg_split('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $orConstraint);
foreach ($andSplit as $andConstraint) {
$constraints[] = $andConstraint;
}
}


$match = false;
foreach ($constraints as $constraint) {
if (preg_match('{^[^@]*?@('.implode('|', array_keys($stabilities)).')$}i', $constraint, $match)) {
$name = strtolower($reqName);
$stability = $stabilities[VersionParser::normalizeStability($match[1])];

if (isset($stabilityFlags[$name]) && $stabilityFlags[$name] > $stability) {
continue;
}
$stabilityFlags[$name] = $stability;
$match = true;
}
}

if ($match) {
continue;
}

foreach ($constraints as $constraint) {


$reqVersion = preg_replace('{^([^,\s@]+) as .+$}', '$1', $constraint);
if (preg_match('{^[^,\s@]+$}', $reqVersion) && 'stable' !== ($stabilityName = VersionParser::parseStability($reqVersion))) {
$name = strtolower($reqName);
$stability = $stabilities[$stabilityName];
if ((isset($stabilityFlags[$name]) && $stabilityFlags[$name] > $stability) || ($minimumStability > $stability)) {
continue;
}
$stabilityFlags[$name] = $stability;
}
}
}

return $stabilityFlags;
}









public static function extractReferences(array $requires, array $references)
{
foreach ($requires as $reqName => $reqVersion) {
$reqVersion = preg_replace('{^([^,\s@]+) as .+$}', '$1', $reqVersion);
if (preg_match('{^[^,\s@]+?#([a-f0-9]+)$}', $reqVersion, $match) && 'dev' === VersionParser::parseStability($reqVersion)) {
$name = strtolower($reqName);
$references[$name] = $match[1];
}
}

return $references;
}
}
