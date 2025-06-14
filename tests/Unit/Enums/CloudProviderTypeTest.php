<?php

use App\Enums\CloudProviderType;

test('regions method returns regions grouped by continent for each provider', function () {
    foreach (CloudProviderType::cases() as $provider) {
        $regions = $provider->regions();

        expect($regions)->toBeArray();
        expect($regions)->not->toBeEmpty();

        foreach ($regions as $continent => $continentRegions) {
            expect($continent)->toBeString();
            expect($continentRegions)->toBeArray();
            expect($continentRegions)->not->toBeEmpty();

            foreach ($continentRegions as $region) {
                expect($region)->toHaveKeys(['name', 'slug']);
                expect($region['name'])->toBeString();
                expect($region['slug'])->toBeString();
                expect($region['name'])->not->toBeEmpty();
                expect($region['slug'])->not->toBeEmpty();
            }
        }
    }
});

test('allRegions method returns all provider regions grouped by continent', function () {
    $allRegions = CloudProviderType::allRegions();

    expect($allRegions)->toBeArray();
    expect($allRegions)->toHaveKeys(['aws', 'hetzner', 'leaseweb', 'google_cloud', 'digital_ocean', 'linode', 'vultr', 'ovh']);

    foreach ($allRegions as $regionsByContinent) {
        expect($regionsByContinent)->toBeArray();
        expect($regionsByContinent)->not->toBeEmpty();

        foreach ($regionsByContinent as $continent => $regions) {
            expect($continent)->toBeString();
            expect($regions)->toBeArray();
            expect($regions)->not->toBeEmpty();

            foreach ($regions as $region) {
                expect($region)->toHaveKeys(['name', 'slug']);
                expect($region['name'])->toBeString();
                expect($region['slug'])->toBeString();
            }
        }
    }
});

test('flatRegions method returns flat array of regions', function () {
    foreach (CloudProviderType::cases() as $provider) {
        $flatRegions = $provider->flatRegions();

        expect($flatRegions)->toBeArray();
        expect($flatRegions)->not->toBeEmpty();

        foreach ($flatRegions as $region) {
            expect($region)->toHaveKeys(['name', 'slug']);
            expect($region['name'])->toBeString();
            expect($region['slug'])->toBeString();
            expect($region['name'])->not->toBeEmpty();
            expect($region['slug'])->not->toBeEmpty();
        }
    }
});

test('hetzner regions are accurate and grouped by continent', function () {
    $regions = CloudProviderType::HETZNER->regions();

    expect($regions)->toHaveKey('Europe');
    expect($regions)->toHaveKey('North America');
    expect($regions)->toHaveKey('Asia Pacific');

    $flatRegions = CloudProviderType::HETZNER->flatRegions();
    expect($flatRegions)->toContain(['name' => 'Falkenstein, Germany', 'slug' => 'fsn1']);
    expect($flatRegions)->toContain(['name' => 'Nuremberg, Germany', 'slug' => 'nbg1']);
    expect($flatRegions)->toContain(['name' => 'Helsinki, Finland', 'slug' => 'hel1']);
    expect($flatRegions)->toContain(['name' => 'Ashburn, VA, USA', 'slug' => 'ash']);
    expect($flatRegions)->toContain(['name' => 'Hillsboro, OR, USA', 'slug' => 'hil']);
    expect($flatRegions)->toContain(['name' => 'Singapore', 'slug' => 'sin']);
});

test('digital ocean regions are accurate and grouped by continent', function () {
    $regions = CloudProviderType::DIGITAL_OCEAN->regions();

    expect($regions)->toHaveKey('North America');
    expect($regions)->toHaveKey('Europe');
    expect($regions)->toHaveKey('Asia Pacific');

    $flatRegions = CloudProviderType::DIGITAL_OCEAN->flatRegions();
    expect($flatRegions)->toContain(['name' => 'New York 1', 'slug' => 'nyc1']);
    expect($flatRegions)->toContain(['name' => 'New York 2', 'slug' => 'nyc2']);
    expect($flatRegions)->toContain(['name' => 'New York 3', 'slug' => 'nyc3']);
    expect($flatRegions)->toContain(['name' => 'San Francisco 2', 'slug' => 'sfo2']);
    expect($flatRegions)->toContain(['name' => 'San Francisco 3', 'slug' => 'sfo3']);
    expect($flatRegions)->toContain(['name' => 'Amsterdam 3', 'slug' => 'ams3']);
    expect($flatRegions)->toContain(['name' => 'London 1', 'slug' => 'lon1']);
    expect($flatRegions)->toContain(['name' => 'Frankfurt 1', 'slug' => 'fra1']);
    expect($flatRegions)->toContain(['name' => 'Singapore 1', 'slug' => 'sgp1']);
    expect($flatRegions)->toContain(['name' => 'Toronto 1', 'slug' => 'tor1']);
    expect($flatRegions)->toContain(['name' => 'Bangalore 1', 'slug' => 'blr1']);
    expect($flatRegions)->toContain(['name' => 'Sydney 1', 'slug' => 'syd1']);
    expect($flatRegions)->toContain(['name' => 'Atlanta 1', 'slug' => 'atl1']);
});

test('aws regions include major locations and are grouped by continent', function () {
    $regions = CloudProviderType::AWS->regions();

    expect($regions)->toHaveKey('North America');
    expect($regions)->toHaveKey('Europe');
    expect($regions)->toHaveKey('Asia Pacific');
    expect($regions)->toHaveKey('Middle East');
    expect($regions)->toHaveKey('Africa');
    expect($regions)->toHaveKey('South America');

    $flatRegions = CloudProviderType::AWS->flatRegions();
    expect($flatRegions)->toContain(['name' => 'US East (N. Virginia)', 'slug' => 'us-east-1']);
    expect($flatRegions)->toContain(['name' => 'US West (Oregon)', 'slug' => 'us-west-2']);
    expect($flatRegions)->toContain(['name' => 'Europe (Ireland)', 'slug' => 'eu-west-1']);
    expect($flatRegions)->toContain(['name' => 'Europe (Frankfurt)', 'slug' => 'eu-central-1']);
    expect($flatRegions)->toContain(['name' => 'Asia Pacific (Singapore)', 'slug' => 'ap-southeast-1']);
    expect($flatRegions)->toContain(['name' => 'Asia Pacific (Tokyo)', 'slug' => 'ap-northeast-1']);
});

test('continent grouping is consistent across providers', function () {
    $expectedContinents = ['North America', 'Europe', 'Asia Pacific'];

    foreach (CloudProviderType::cases() as $provider) {
        $regions = $provider->regions();
        $continents = array_keys($regions);

        // Each provider should have at least one of the expected continents
        $hasExpectedContinent = !empty(array_intersect($continents, $expectedContinents));
        expect($hasExpectedContinent)->toBeTrue("Provider {$provider->value} should have at least one expected continent");

        // All continent names should be strings
        foreach ($continents as $continent) {
            expect($continent)->toBeString();
            expect($continent)->not->toBeEmpty();
        }
    }
});

test('credentialFields method returns correct field definitions for each provider', function () {
    foreach (CloudProviderType::cases() as $provider) {
        $fields = $provider->credentialFields();

        expect($fields)->toBeArray();
        expect($fields)->not->toBeEmpty();

        foreach ($fields as $field) {
            expect($field)->toHaveKeys(['name', 'label', 'type', 'placeholder', 'required']);
            expect($field['name'])->toBeString();
            expect($field['label'])->toBeString();
            expect($field['type'])->toBeIn(['text', 'password', 'textarea']);
            expect($field['placeholder'])->toBeString();
            expect($field['required'])->toBeBool();
        }
    }
});

test('aws credential fields include access key and secret key', function () {
    $fields = CloudProviderType::AWS->credentialFields();

    expect($fields)->toHaveCount(2);
    expect($fields[0]['name'])->toBe('access_key');
    expect($fields[0]['label'])->toBe('Access Key ID');
    expect($fields[0]['type'])->toBe('text');
    expect($fields[0]['required'])->toBeTrue();

    expect($fields[1]['name'])->toBe('secret_key');
    expect($fields[1]['label'])->toBe('Secret Access Key');
    expect($fields[1]['type'])->toBe('password');
    expect($fields[1]['required'])->toBeTrue();
});

test('hetzner credential fields include single token', function () {
    $fields = CloudProviderType::HETZNER->credentialFields();

    expect($fields)->toHaveCount(1);
    expect($fields[0]['name'])->toBe('token');
    expect($fields[0]['label'])->toBe('API Token');
    expect($fields[0]['type'])->toBe('password');
    expect($fields[0]['required'])->toBeTrue();
});

test('digital ocean credential fields include single token', function () {
    $fields = CloudProviderType::DIGITAL_OCEAN->credentialFields();

    expect($fields)->toHaveCount(1);
    expect($fields[0]['name'])->toBe('token');
    expect($fields[0]['label'])->toBe('Personal Access Token');
    expect($fields[0]['type'])->toBe('password');
    expect($fields[0]['required'])->toBeTrue();
});

test('ovh credential fields include application key, secret, and consumer key', function () {
    $fields = CloudProviderType::OVH->credentialFields();

    expect($fields)->toHaveCount(3);
    expect($fields[0]['name'])->toBe('application_key');
    expect($fields[0]['label'])->toBe('Application Key');
    expect($fields[0]['type'])->toBe('text');
    expect($fields[0]['required'])->toBeTrue();

    expect($fields[1]['name'])->toBe('application_secret');
    expect($fields[1]['label'])->toBe('Application Secret');
    expect($fields[1]['type'])->toBe('password');
    expect($fields[1]['required'])->toBeTrue();

    expect($fields[2]['name'])->toBe('consumer_key');
    expect($fields[2]['label'])->toBe('Consumer Key');
    expect($fields[2]['type'])->toBe('password');
    expect($fields[2]['required'])->toBeTrue();
});

test('allProviders method includes credential fields', function () {
    $providers = CloudProviderType::allProviders();

    expect($providers)->toBeArray();
    expect($providers)->not->toBeEmpty();

    foreach ($providers as $provider) {
        expect($provider)->toHaveKeys(['type', 'name', 'implemented', 'credentialFields']);
        expect($provider['credentialFields'])->toBeArray();
        expect($provider['credentialFields'])->not->toBeEmpty();

        foreach ($provider['credentialFields'] as $field) {
            expect($field)->toHaveKeys(['name', 'label', 'type', 'placeholder', 'required']);
        }
    }
});
