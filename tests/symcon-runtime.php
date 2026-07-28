<?php

declare(strict_types=1);

const IPS_KERNELSTARTED = 10001;
const KR_READY = 10103;
const OM_UNREGISTER = 10402;

$testKernelRunlevel = KR_READY;

function IPS_GetKernelRunlevel(): int
{
    global $testKernelRunlevel;

    return $testKernelRunlevel;
}

function TestSetKernelRunlevel(int $runlevel): void
{
    global $testKernelRunlevel;

    $testKernelRunlevel = $runlevel;
}
