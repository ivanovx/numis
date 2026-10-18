<?php

test('numis:import rejects malformed json', function () {
    $path = tempnam(sys_get_temp_dir(), 'numis_import_');
    file_put_contents($path, '{invalid json');

    try {
        $this->artisan('numis:import', ['path' => $path])
            ->expectsOutput('Could not parse JSON file.')
            ->assertExitCode(1);
    } finally {
        unlink($path);
    }
});

test('numis:import rejects a json document with an invalid root type', function () {
    $path = tempnam(sys_get_temp_dir(), 'numis_import_');
    file_put_contents($path, json_encode('invalid root'));

    try {
        $this->artisan('numis:import', ['path' => $path])
            ->expectsOutput('The JSON file must contain an object with series and coins arrays.')
            ->assertExitCode(1);
    } finally {
        unlink($path);
    }
});
