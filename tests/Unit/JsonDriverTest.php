<?php

declare(strict_types=1);

it('gets nested folder as subdrivers', function () {
    $driver = $this->getJsonDriver();

    $subDrivers = $driver->getSubDrivers();

    $subDriversKeys = collect($subDrivers)->map(fn ($driver) => mb_rtrim($driver->getKey(), '/\\'))->all();

    expect($subDriversKeys)->toEqualCanonicalizing([
        $driver->storage->path($this->formatPath('package')),
    ]);
});
