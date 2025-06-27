<?php

use App\Enums\CloudProviderType;

test('cloud provider regions include flag property', function () {
    $regions = CloudProviderType::HETZNER->regions();

    expect($regions)->toBeArray()
        ->and($regions)->toHaveKey('Europe')
        ->and($regions['Europe'])->toBeArray()
        ->and($regions['Europe'][0])->toHaveKeys(['name', 'slug', 'flag'])
        ->and($regions['Europe'][0]['flag'])->toStartWith('/flags/')
        ->and($regions['Europe'][0]['flag'])->toEndWith('.svg');
});

test('all cloud provider regions include flag property', function () {
    $allRegions = CloudProviderType::allRegions();

    foreach ($allRegions as $regionsByContinent) {
        foreach ($regionsByContinent as $regions) {
            foreach ($regions as $region) {
                expect($region)->toHaveKeys(['name', 'slug', 'flag'])
                    ->and($region['flag'])->toStartWith('/flags/')
                    ->and($region['flag'])->toEndWith('.svg');
            }
        }
    }
});

test('flat regions include flag property', function () {
    $flatRegions = CloudProviderType::HETZNER->flatRegions();

    expect($flatRegions)->toBeArray();

    foreach ($flatRegions as $region) {
        expect($region)->toHaveKeys(['name', 'slug', 'flag'])
            ->and($region['flag'])->toStartWith('/flags/')
            ->and($region['flag'])->toEndWith('.svg');
    }
});
