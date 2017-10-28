<?php
namespace AppBundle\Service;

use Negotiation\Exception\InvalidArgument;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use AppBundle\Shared\SoundFileMetadata;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;

class SoundFileManipulatorFFMPEG
{
    private $ffmpeg = "ffmpeg";

    public function normalize($inputFilePath, $outputFilePath) {
        $meanVolume = $this->getMeanVolume($inputFilePath);
        $gain = $this->calculateGainValue($meanVolume);
        return $this->adjustVolume($inputFilePath, $gain, $outputFilePath);
    }

    // TODO: Return Class (Value and Success)
    public function getDuration($soundFilePath) {
        $command = "{$this->ffmpeg} -i {$soundFilePath} 2>&1 | grep Duration | awk '{print $2}' | tr -d , | cut -c4-";
        return $this->executeProcess($command);
    }

    public function setMetadata($inputFilePath, SoundFileMetadata $metaData, $outputFilePath) {
        $command = "{$this->ffmpeg} -i {$inputFilePath} \
                    -codec copy \
                    -y \
                    -metadata title='{$metaData->title}' \
                    -metadata artist='{$metaData->artist}' \
                    -metadata author='{$metaData->artist}' \
                    -metadata album='{$metaData->album}' \
                    -metadata date='' \
                    -metadata comment='' \
                    -metadata track='' \
                    -metadata genre='' \
                    {$outputFilePath}";
        $processResult = $this->executeProcess($command);
        return $processResult->isSuccessful();
    }

    // TODO: Return Class (Value and Success)
    private function getMeanVolume($inputFilePath){
        $command = "{$this->ffmpeg} -i {$inputFilePath} -af volumedetect -f null /dev/null 2>&1 | grep 'volume:'";
        $processResult = $this->executeProcess($command);
        $outputString = $processResult->getOutput();
        $stringMeanVolume = $this->extractMeanVolumeFromString($outputString);
        return doubleval($stringMeanVolume);
    }

    private function extractMeanVolumeFromString($stringInput) {
        $regExPattern = '/mean_volume: (-{0,1}\d*\.\d*)/';
        preg_match($regExPattern, $stringInput, $matches);

        if(count($matches) != 2) {
            throw new NoSuchIndexException();
        }

        return $matches[1];
    }

    private function calculateGainValue($soundMeanVolume) {
        $nMV = -21.5; //Normalized Mean Volume
        $gain = 0;
        if($soundMeanVolume < $nMV){
            //Increase volume
            $gain = abs($soundMeanVolume) - abs($nMV);
        } else if($soundMeanVolume > $nMV){
            //Decrease volume
            $gain = $nMV + abs($soundMeanVolume);
        }
        return $gain;
    }

    private function adjustVolume($inputFilePath, $gain, $outputFilePath) {
        // mono: -ac 1
        $command = "{$this->ffmpeg} -i {$inputFilePath} -y -ac 1 -af volume={$gain}dB {$outputFilePath}";
        $processResult = $this->executeProcess($command);
        return $processResult->isSuccessful();
    }

    /**
     * @param string $command
     * @return ProcessResult
     */
    private function executeProcess($command) {
        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return new ProcessResult($process->getExitCode(), $process->getOutput());
    }
}

class ProcessResult
{
    private $exitCode;
    private $output;

    /**
     * ProcessResult constructor.
     * @param int|null $exitCode
     * @param string $output
     */
    public function __construct($exitCode, $output)
    {
        $this->exitCode = $exitCode;
        $this->output = $output;
    }

    /**
     * @return int|null Exitcode of Process
     */
    public function getExitCode() {
        return $this->exitCode;
    }

    /**
     * @return string|null Output of Process
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * @return bool
     */
    public function isSuccessful() {
        return $this->exitCode === 0;
    }
}

?>