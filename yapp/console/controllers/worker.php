<?php

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('revert_string','simpleRev');
//$worker->work();

while($worker->work());

function simpleRev($job){
    $content = $job->workload();
    return mb_strtoupper(strrev($content));
}