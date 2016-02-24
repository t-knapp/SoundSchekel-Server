<?php

define("ffmpeg", "~/bin/ffmpeg");

// Returns duration of $mp3File in mm:ss.mm
function getDuration($mp3File){
    $output = array();
    exec(ffmpeg . " -i {$mp3File} 2>&1 | grep Duration | awk '{print $2}' | tr -d , | cut -c4-", $output);
    return $output[0];
}


// Metadata
function metadata($target_path, $key, $title, $finalPathName){
    $output = array();
    $code = 1337;
    exec(ffmpeg . " -i {$target_path}  -metadata title='{$title}' -metadata artist='Soundschekel' -metadata album='{$key}' {$finalPathName}.mp3", $output, $code);
    
    if($code !== 0)
        return false;

    return rename($finalPathName . ".mp3", $finalPathName);
}

// Returns array of volumes
function getVolumes($mp3File){
    $output = array();

    exec(ffmpeg . " -i {$mp3File} -af volumedetect -f null /dev/null 2>&1 | grep 'volume:'", $output);

    $result = array();
    foreach($output as $volume){
        $split = explode(": ", $volume);
        $dbKey = explode(" ", $split[0])[3];
        $dbVal = str_replace(" dB", "", $split[1]);
        //array_push($result, array($dbKey => doubleval($dbVal)));
        $result[$dbKey] = $dbVal;
    }

    return $result;
}

// Creates file with normalized postfix.
function adjustVolume($mp3File, $gain){
    $output = array();
    // mono: -ac 1
    exec(ffmpeg . " -i {$mp3File} -ac 1 -af volume={$gain}dB {$mp3File}.mp3", $output);
    return $output;
}

function normalize($mp3File){
    $nMV = -21.5; //Normalized Mean Volume
    $volumes = getVolumes($mp3File);
    //debug($volumes);

    $mean = doubleval($volumes['mean_volume']);
    //echo "Mean: {$mean}" . PHP_EOL;

    $gain = 0;
    if($mean < $nMV){
        //Increase volume
        $gain = abs($mean) - abs($nMV);
    } else if($mean > $nMV){
        //Decrease volume
        $gain = $nMV + abs($mean); 
    }
    debug("normalize() gain = $gain");

    adjustVolume($mp3File, $gain);
}

/*
function debug($data){
    echo "<pre>"; print_r($data); echo "</pre>";
}
*/
