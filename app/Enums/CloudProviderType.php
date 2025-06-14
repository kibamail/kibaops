<?php

namespace App\Enums;

enum CloudProviderType: string
{
    case AWS = 'aws';
    case HETZNER = 'hetzner';
    case LEASEWEB = 'leaseweb';
    case GOOGLE_CLOUD = 'google_cloud';
    case DIGITAL_OCEAN = 'digital_ocean';
    case LINODE = 'linode';
    case VULTR = 'vultr';

    /**
     * Get the human-readable label for the cloud provider type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AWS => 'Amazon Web Services',
            self::HETZNER => 'Hetzner Cloud',
            self::LEASEWEB => 'LeaseWeb',
            self::GOOGLE_CLOUD => 'Google Cloud Platform',
            self::DIGITAL_OCEAN => 'DigitalOcean',
            self::LINODE => 'Linode',
            self::VULTR => 'Vultr',
        };
    }

    /**
     * Get all available cloud provider type values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get supported regions for the cloud provider type grouped by continent.
     * Returns an array with continent keys and regions with 'name' and 'slug' keys.
     */
    public function regions(): array
    {
        return match ($this) {
            self::AWS => [
                'North America' => [
                    ['name' => 'US East (N. Virginia)', 'slug' => 'us-east-1'],
                    ['name' => 'US East (Ohio)', 'slug' => 'us-east-2'],
                    ['name' => 'US West (N. California)', 'slug' => 'us-west-1'],
                    ['name' => 'US West (Oregon)', 'slug' => 'us-west-2'],
                    ['name' => 'Canada (Central)', 'slug' => 'ca-central-1'],
                    ['name' => 'Canada West (Calgary)', 'slug' => 'ca-west-1'],
                    ['name' => 'Mexico (Central)', 'slug' => 'mx-central-1'],
                ],
                'South America' => [
                    ['name' => 'South America (São Paulo)', 'slug' => 'sa-east-1'],
                ],
                'Europe' => [
                    ['name' => 'Europe (Ireland)', 'slug' => 'eu-west-1'],
                    ['name' => 'Europe (London)', 'slug' => 'eu-west-2'],
                    ['name' => 'Europe (Paris)', 'slug' => 'eu-west-3'],
                    ['name' => 'Europe (Frankfurt)', 'slug' => 'eu-central-1'],
                    ['name' => 'Europe (Stockholm)', 'slug' => 'eu-north-1'],
                    ['name' => 'Europe (Milan)', 'slug' => 'eu-south-1'],
                    ['name' => 'Europe (Spain)', 'slug' => 'eu-south-2'],
                    ['name' => 'Europe (Zurich)', 'slug' => 'eu-central-2'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Asia Pacific (Mumbai)', 'slug' => 'ap-south-1'],
                    ['name' => 'Asia Pacific (Hyderabad)', 'slug' => 'ap-south-2'],
                    ['name' => 'Asia Pacific (Singapore)', 'slug' => 'ap-southeast-1'],
                    ['name' => 'Asia Pacific (Jakarta)', 'slug' => 'ap-southeast-3'],
                    ['name' => 'Asia Pacific (Sydney)', 'slug' => 'ap-southeast-2'],
                    ['name' => 'Asia Pacific (Melbourne)', 'slug' => 'ap-southeast-4'],
                    ['name' => 'Asia Pacific (Tokyo)', 'slug' => 'ap-northeast-1'],
                    ['name' => 'Asia Pacific (Osaka)', 'slug' => 'ap-northeast-3'],
                    ['name' => 'Asia Pacific (Seoul)', 'slug' => 'ap-northeast-2'],
                    ['name' => 'Asia Pacific (Hong Kong)', 'slug' => 'ap-east-1'],
                    ['name' => 'Asia Pacific (Taiwan)', 'slug' => 'ap-northeast-4'],
                    ['name' => 'Asia Pacific (Thailand)', 'slug' => 'ap-southeast-5'],
                    ['name' => 'Asia Pacific (Malaysia)', 'slug' => 'ap-southeast-6'],
                ],
                'Middle East' => [
                    ['name' => 'Middle East (Bahrain)', 'slug' => 'me-south-1'],
                    ['name' => 'Middle East (UAE)', 'slug' => 'me-central-1'],
                    ['name' => 'Israel (Tel Aviv)', 'slug' => 'il-central-1'],
                ],
                'Africa' => [
                    ['name' => 'Africa (Cape Town)', 'slug' => 'af-south-1'],
                ],
            ],
            self::HETZNER => [
                'Europe' => [
                    ['name' => 'Falkenstein, Germany', 'slug' => 'fsn1'],
                    ['name' => 'Nuremberg, Germany', 'slug' => 'nbg1'],
                    ['name' => 'Helsinki, Finland', 'slug' => 'hel1'],
                ],
                'North America' => [
                    ['name' => 'Ashburn, VA, USA', 'slug' => 'ash'],
                    ['name' => 'Hillsboro, OR, USA', 'slug' => 'hil'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore', 'slug' => 'sin'],
                ],
            ],
            self::DIGITAL_OCEAN => [
                'North America' => [
                    ['name' => 'New York 1', 'slug' => 'nyc1'],
                    ['name' => 'New York 2', 'slug' => 'nyc2'],
                    ['name' => 'New York 3', 'slug' => 'nyc3'],
                    ['name' => 'San Francisco 2', 'slug' => 'sfo2'],
                    ['name' => 'San Francisco 3', 'slug' => 'sfo3'],
                    ['name' => 'Toronto 1', 'slug' => 'tor1'],
                    ['name' => 'Atlanta 1', 'slug' => 'atl1'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam 3', 'slug' => 'ams3'],
                    ['name' => 'London 1', 'slug' => 'lon1'],
                    ['name' => 'Frankfurt 1', 'slug' => 'fra1'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore 1', 'slug' => 'sgp1'],
                    ['name' => 'Bangalore 1', 'slug' => 'blr1'],
                    ['name' => 'Sydney 1', 'slug' => 'syd1'],
                ],
            ],
            self::GOOGLE_CLOUD => [
                'North America' => [
                    ['name' => 'Iowa (us-central1)', 'slug' => 'us-central1'],
                    ['name' => 'South Carolina (us-east1)', 'slug' => 'us-east1'],
                    ['name' => 'N. Virginia (us-east4)', 'slug' => 'us-east4'],
                    ['name' => 'Columbus (us-east5)', 'slug' => 'us-east5'],
                    ['name' => 'Dallas (us-south1)', 'slug' => 'us-south1'],
                    ['name' => 'Oregon (us-west1)', 'slug' => 'us-west1'],
                    ['name' => 'Los Angeles (us-west2)', 'slug' => 'us-west2'],
                    ['name' => 'Salt Lake City (us-west3)', 'slug' => 'us-west3'],
                    ['name' => 'Las Vegas (us-west4)', 'slug' => 'us-west4'],
                    ['name' => 'Montreal (northamerica-northeast1)', 'slug' => 'northamerica-northeast1'],
                    ['name' => 'Toronto (northamerica-northeast2)', 'slug' => 'northamerica-northeast2'],
                ],
                'South America' => [
                    ['name' => 'São Paulo (southamerica-east1)', 'slug' => 'southamerica-east1'],
                    ['name' => 'Santiago (southamerica-west1)', 'slug' => 'southamerica-west1'],
                ],
                'Europe' => [
                    ['name' => 'Belgium (europe-west1)', 'slug' => 'europe-west1'],
                    ['name' => 'London (europe-west2)', 'slug' => 'europe-west2'],
                    ['name' => 'Frankfurt (europe-west3)', 'slug' => 'europe-west3'],
                    ['name' => 'Netherlands (europe-west4)', 'slug' => 'europe-west4'],
                    ['name' => 'Zurich (europe-west6)', 'slug' => 'europe-west6'],
                    ['name' => 'Milan (europe-west8)', 'slug' => 'europe-west8'],
                    ['name' => 'Paris (europe-west9)', 'slug' => 'europe-west9'],
                    ['name' => 'Berlin (europe-west10)', 'slug' => 'europe-west10'],
                    ['name' => 'Turin (europe-west12)', 'slug' => 'europe-west12'],
                    ['name' => 'Finland (europe-north1)', 'slug' => 'europe-north1'],
                    ['name' => 'Warsaw (europe-central2)', 'slug' => 'europe-central2'],
                    ['name' => 'Madrid (europe-southwest1)', 'slug' => 'europe-southwest1'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Mumbai (asia-south1)', 'slug' => 'asia-south1'],
                    ['name' => 'Delhi (asia-south2)', 'slug' => 'asia-south2'],
                    ['name' => 'Singapore (asia-southeast1)', 'slug' => 'asia-southeast1'],
                    ['name' => 'Jakarta (asia-southeast2)', 'slug' => 'asia-southeast2'],
                    ['name' => 'Hong Kong (asia-east2)', 'slug' => 'asia-east2'],
                    ['name' => 'Taiwan (asia-east1)', 'slug' => 'asia-east1'],
                    ['name' => 'Tokyo (asia-northeast1)', 'slug' => 'asia-northeast1'],
                    ['name' => 'Osaka (asia-northeast2)', 'slug' => 'asia-northeast2'],
                    ['name' => 'Seoul (asia-northeast3)', 'slug' => 'asia-northeast3'],
                    ['name' => 'Sydney (australia-southeast1)', 'slug' => 'australia-southeast1'],
                    ['name' => 'Melbourne (australia-southeast2)', 'slug' => 'australia-southeast2'],
                ],
                'Middle East' => [
                    ['name' => 'Tel Aviv (me-west1)', 'slug' => 'me-west1'],
                    ['name' => 'Doha (me-central1)', 'slug' => 'me-central1'],
                    ['name' => 'Dammam (me-central2)', 'slug' => 'me-central2'],
                ],
                'Africa' => [
                    ['name' => 'Johannesburg (africa-south1)', 'slug' => 'africa-south1'],
                ],
            ],
            self::VULTR => [
                'North America' => [
                    ['name' => 'Atlanta, GA', 'slug' => 'atl'],
                    ['name' => 'Chicago, IL', 'slug' => 'ord'],
                    ['name' => 'Dallas, TX', 'slug' => 'dfw'],
                    ['name' => 'Honolulu, HI', 'slug' => 'hnl'],
                    ['name' => 'Los Angeles, CA', 'slug' => 'lax'],
                    ['name' => 'Miami, FL', 'slug' => 'mia'],
                    ['name' => 'New York Area', 'slug' => 'ewr'],
                    ['name' => 'San Francisco Bay Area, CA', 'slug' => 'sjc'],
                    ['name' => 'Seattle, WA', 'slug' => 'sea'],
                    ['name' => 'Toronto, Canada', 'slug' => 'yto'],
                    ['name' => 'Mexico City, Mexico', 'slug' => 'mex'],
                ],
                'South America' => [
                    ['name' => 'São Paulo, Brazil', 'slug' => 'sao'],
                    ['name' => 'Santiago, Chile', 'slug' => 'scl'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'ams'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'fra'],
                    ['name' => 'London, United Kingdom', 'slug' => 'lhr'],
                    ['name' => 'Madrid, Spain', 'slug' => 'mad'],
                    ['name' => 'Manchester, United Kingdom', 'slug' => 'man'],
                    ['name' => 'Paris, France', 'slug' => 'cdg'],
                    ['name' => 'Stockholm, Sweden', 'slug' => 'arn'],
                    ['name' => 'Warsaw, Poland', 'slug' => 'waw'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Tokyo, Japan', 'slug' => 'nrt'],
                    ['name' => 'Osaka, Japan', 'slug' => 'itm'],
                    ['name' => 'Seoul, South Korea', 'slug' => 'icn'],
                    ['name' => 'Singapore', 'slug' => 'sgp'],
                    ['name' => 'Mumbai, India', 'slug' => 'bom'],
                    ['name' => 'Delhi NCR, India', 'slug' => 'del'],
                    ['name' => 'Bangalore, India', 'slug' => 'blr'],
                    ['name' => 'Sydney, Australia', 'slug' => 'syd'],
                    ['name' => 'Melbourne, Australia', 'slug' => 'mel'],
                ],
                'Middle East' => [
                    ['name' => 'Tel Aviv-Yafo, Israel', 'slug' => 'tlv'],
                ],
                'Africa' => [
                    ['name' => 'Johannesburg, South Africa', 'slug' => 'jnb'],
                ],
            ],
            self::LINODE => [
                'North America' => [
                    ['name' => 'Newark, NJ', 'slug' => 'us-east'],
                    ['name' => 'Atlanta, GA', 'slug' => 'us-southeast'],
                    ['name' => 'Dallas, TX', 'slug' => 'us-central'],
                    ['name' => 'Fremont, CA', 'slug' => 'us-west'],
                    ['name' => 'Chicago, IL', 'slug' => 'us-ord'],
                    ['name' => 'Los Angeles, CA', 'slug' => 'us-lax'],
                    ['name' => 'Miami, FL', 'slug' => 'us-mia'],
                    ['name' => 'Seattle, WA', 'slug' => 'us-sea'],
                    ['name' => 'Washington, D.C.', 'slug' => 'us-iad'],
                    ['name' => 'Toronto, Canada', 'slug' => 'ca-central'],
                ],
                'South America' => [
                    ['name' => 'São Paulo, Brazil', 'slug' => 'br-gru'],
                ],
                'Europe' => [
                    ['name' => 'London, UK', 'slug' => 'eu-west'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'eu-central'],
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'nl-ams'],
                    ['name' => 'Stockholm, Sweden', 'slug' => 'se-sto'],
                    ['name' => 'Paris, France', 'slug' => 'fr-par'],
                    ['name' => 'Milan, Italy', 'slug' => 'it-mil'],
                    ['name' => 'Madrid, Spain', 'slug' => 'es-mad'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Mumbai, India', 'slug' => 'ap-west'],
                    ['name' => 'Chennai, India', 'slug' => 'in-maa'],
                    ['name' => 'Singapore', 'slug' => 'ap-south'],
                    ['name' => 'Tokyo, Japan', 'slug' => 'ap-northeast'],
                    ['name' => 'Osaka, Japan', 'slug' => 'jp-osa'],
                    ['name' => 'Jakarta, Indonesia', 'slug' => 'id-cgk'],
                    ['name' => 'Sydney, Australia', 'slug' => 'ap-southeast'],
                    ['name' => 'Melbourne, Australia', 'slug' => 'au-mel'],
                ],
            ],
            self::LEASEWEB => [
                'North America' => [
                    ['name' => 'Washington, D.C., USA', 'slug' => 'wdc-02'],
                    ['name' => 'San Francisco, CA, USA', 'slug' => 'sfo-01'],
                    ['name' => 'Montreal, Canada', 'slug' => 'yul-01'],
                    ['name' => 'Miami, FL, USA', 'slug' => 'mia-01'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'ams-01'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'fra-01'],
                    ['name' => 'London, United Kingdom', 'slug' => 'lon-01'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore', 'slug' => 'sin-01'],
                    ['name' => 'Tokyo, Japan', 'slug' => 'tyo-10'],
                ],
            ],
        };
    }

    /**
     * Get all cloud provider types formatted as options for form selects.
     * Returns an array with 'value' and 'label' keys for each type.
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    /**
     * Get the list of cloud provider types that have been implemented.
     * Only these types can be used for credential verification.
     */
    public static function implemented(): array
    {
        return [
            self::HETZNER,
            self::DIGITAL_OCEAN,
        ];
    }

    /**
     * Get regions in a flat array format (for backward compatibility).
     * Returns an array of regions with 'name' and 'slug' keys.
     */
    public function flatRegions(): array
    {
        $flatRegions = [];
        foreach ($this->regions() as $regions) {
            $flatRegions = array_merge($flatRegions, $regions);
        }
        return $flatRegions;
    }

    /**
     * Get all cloud provider regions data for frontend consumption.
     * Returns an associative array with provider types as keys and their regions grouped by continent.
     */
    public static function allRegions(): array
    {
        $regions = [];
        foreach (self::cases() as $case) {
            $regions[$case->value] = $case->regions();
        }
        return $regions;
    }
}
