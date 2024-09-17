<?php










namespace Symfony\Component\Process\Pipes;

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;











class WindowsPipes extends AbstractPipes
{
private $files = array();
private $fileHandles = array();
private $lockHandles = array();
private $readBytes = array(
Process::STDOUT => 0,
Process::STDERR => 0,
);
private $disableOutput;

public function __construct($disableOutput, $input)
{
$this->disableOutput = (bool) $disableOutput;

if (!$this->disableOutput) {




$pipes = array(
Process::STDOUT => Process::OUT,
Process::STDERR => Process::ERR,
);
$tmpDir = sys_get_temp_dir();
$lastError = 'unknown reason';
set_error_handler(function ($type, $msg) use (&$lastError) { $lastError = $msg; });
for ($i = 0;; ++$i) {
foreach ($pipes as $pipe => $name) {
$file = sprintf('%s\\sf_proc_%02X.%s', $tmpDir, $i, $name);

if (!$h = fopen($file.'.lock', 'w')) {
restore_error_handler();
throw new RuntimeException(sprintf('A temporary file could not be opened to write the process output: %s', $lastError));
}
if (!flock($h, LOCK_EX | LOCK_NB)) {
continue 2;
}
if (isset($this->lockHandles[$pipe])) {
flock($this->lockHandles[$pipe], LOCK_UN);
fclose($this->lockHandles[$pipe]);
}
$this->lockHandles[$pipe] = $h;

if (!fclose(fopen($file, 'w')) || !$h = fopen($file, 'r')) {
flock($this->lockHandles[$pipe], LOCK_UN);
fclose($this->lockHandles[$pipe]);
unset($this->lockHandles[$pipe]);
continue 2;
}
$this->fileHandles[$pipe] = $h;
$this->files[$pipe] = $file;
}
break;
}
restore_error_handler();
}

parent::__construct($input);
}

public function __destruct()
{
$this->close();
}




public function getDescriptors()
{
if ($this->disableOutput) {
$nullstream = fopen('NUL', 'c');

return array(
array('pipe', 'r'),
$nullstream,
$nullstream,
);
}




return array(
array('pipe', 'r'),
array('file', 'NUL', 'w'),
array('file', 'NUL', 'w'),
);
}




public function getFiles()
{
return $this->files;
}




public function readAndWrite($blocking, $close = false)
{
$this->unblock();
$w = $this->write();
$read = $r = $e = array();

if ($blocking) {
if ($w) {
@stream_select($r, $w, $e, 0, Process::TIMEOUT_PRECISION * 1E6);
} elseif ($this->fileHandles) {
usleep(Process::TIMEOUT_PRECISION * 1E6);
}
}
foreach ($this->fileHandles as $type => $fileHandle) {
$data = stream_get_contents($fileHandle, -1, $this->readBytes[$type]);

if (isset($data[0])) {
$this->readBytes[$type] += \strlen($data);
$read[$type] = $data;
}
if ($close) {
ftruncate($fileHandle, 0);
fclose($fileHandle);
flock($this->lockHandles[$type], LOCK_UN);
fclose($this->lockHandles[$type]);
unset($this->fileHandles[$type], $this->lockHandles[$type]);
}
}

return $read;
}




public function areOpen()
{
return $this->pipes && $this->fileHandles;
}




public function close()
{
parent::close();
foreach ($this->fileHandles as $type => $handle) {
ftruncate($handle, 0);
fclose($handle);
flock($this->lockHandles[$type], LOCK_UN);
fclose($this->lockHandles[$type]);
}
$this->fileHandles = $this->lockHandles = array();
}









public static function create(Process $process, $input)
{
return new static($process->isOutputDisabled(), $input);
}
}
