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
    case OVH = 'ovh';

    /**
     * Get the human-readable label for the cloud provider type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AWS => 'Amazon web services',
            self::HETZNER => 'Hetzner cloud',
            self::LEASEWEB => 'Lease web',
            self::GOOGLE_CLOUD => 'Google cloud platform',
            self::DIGITAL_OCEAN => 'Digital ocean',
            self::LINODE => 'Linode',
            self::VULTR => 'Vultr',
            self::OVH => 'OVH',
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
     * Get a description with instructions on how to obtain credentials for this provider.
     * Returns a short description without the documentation link.
     */
    public function description(): string
    {
        return match ($this) {
            self::AWS => 'Create an IAM user with programmatic access in your AWS console.',
            self::HETZNER => 'Generate an API token in your Hetzner Cloud console under Security > API Tokens.',
            self::DIGITAL_OCEAN => 'Create a personal access token in your DigitalOcean control panel under API > Tokens.',
            self::GOOGLE_CLOUD => 'Create a service account and download the JSON key file from Google Cloud Console.',
            self::LEASEWEB => 'Generate an API key in your LeaseWeb customer portal under API Management.',
            self::LINODE => 'Create a personal access token in your Linode Cloud Manager under My Profile > API Tokens.',
            self::VULTR => 'Generate an API key in your Vultr customer portal under Account > API.',
            self::OVH => 'Create API credentials in your OVH control panel under Advanced > API Management.',
        };
    }

    /**
     * Get the documentation link for this cloud provider.
     * Returns the URL to the provider-specific documentation.
     */
    public function documentationLink(): string
    {
        return match ($this) {
            self::AWS => 'https://kibaops.com/docs/providers/aws',
            self::HETZNER => 'https://kibaops.com/docs/providers/hetzner',
            self::DIGITAL_OCEAN => 'https://kibaops.com/docs/providers/digitalocean',
            self::GOOGLE_CLOUD => 'https://kibaops.com/docs/providers/gcp',
            self::LEASEWEB => 'https://kibaops.com/docs/providers/leaseweb',
            self::LINODE => 'https://kibaops.com/docs/providers/linode',
            self::VULTR => 'https://kibaops.com/docs/providers/vultr',
            self::OVH => 'https://kibaops.com/docs/providers/ovh',
        };
    }

    /**
     * Get the credential fields required for this cloud provider type.
     * Returns an array of field definitions with name, label, type, and validation rules.
     */
    public function credentialFields(): array
    {
        return match ($this) {
            self::AWS => [
                [
                    'name' => 'access_key',
                    'label' => 'Access key ID',
                    'type' => 'text',
                    'placeholder' => 'AKIAIOSFODNN7EXAMPLE',
                    'required' => true,
                ],
                [
                    'name' => 'secret_key',
                    'label' => 'Secret access key',
                    'type' => 'password',
                    'placeholder' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                    'required' => true,
                ],
            ],
            self::HETZNER => [
                [
                    'name' => 'token',
                    'label' => 'API token',
                    'type' => 'password',
                    'placeholder' => 'Enter your hetzner cloud api token',
                    'required' => true,
                ],
            ],
            self::DIGITAL_OCEAN => [
                [
                    'name' => 'token',
                    'label' => 'Personal access token',
                    'type' => 'password',
                    'placeholder' => 'Enter your DigitalOcean personal access token',
                    'required' => true,
                ],
            ],
            self::GOOGLE_CLOUD => [
                [
                    'name' => 'service_account_key',
                    'label' => 'Service account key (JSON)',
                    'type' => 'textarea',
                    'placeholder' => 'Paste your service account JSON key here',
                    'required' => true,
                ],
            ],
            self::LEASEWEB => [
                [
                    'name' => 'api_key',
                    'label' => 'API Key',
                    'type' => 'password',
                    'placeholder' => 'Enter your LeaseWeb API key',
                    'required' => true,
                ],
            ],
            self::LINODE => [
                [
                    'name' => 'token',
                    'label' => 'Personal access token',
                    'type' => 'password',
                    'placeholder' => 'Enter your linode personal access token',
                    'required' => true,
                ],
            ],
            self::VULTR => [
                [
                    'name' => 'api_key',
                    'label' => 'API key',
                    'type' => 'password',
                    'placeholder' => 'Enter your vultr api key',
                    'required' => true,
                ],
            ],
            self::OVH => [
                [
                    'name' => 'application_key',
                    'label' => 'Application Key',
                    'type' => 'text',
                    'placeholder' => 'Enter your ovh application key',
                    'required' => true,
                ],
                [
                    'name' => 'application_secret',
                    'label' => 'Application Secret',
                    'type' => 'password',
                    'placeholder' => 'Enter your ovh application secret',
                    'required' => true,
                ],
                [
                    'name' => 'consumer_key',
                    'label' => 'Consumer Key',
                    'type' => 'password',
                    'placeholder' => 'Enter your ovh consumer key',
                    'required' => true,
                ],
            ],
        };
    }

    /**
     * Get supported regions for the cloud provider type grouped by continent.
     * Returns an array with continent keys and regions with 'name', 'slug', and 'flag' keys.
     */
    public function regions(): array
    {
        return match ($this) {
            self::AWS => [
                'North America' => [
                    ['name' => 'US East (N. Virginia)', 'slug' => 'us-east-1', 'flag' => '/flags/us.svg'],
                    ['name' => 'US East (Ohio)', 'slug' => 'us-east-2', 'flag' => '/flags/us.svg'],
                    ['name' => 'US West (N. California)', 'slug' => 'us-west-1', 'flag' => '/flags/us.svg'],
                    ['name' => 'US West (Oregon)', 'slug' => 'us-west-2', 'flag' => '/flags/us.svg'],
                    ['name' => 'Canada (Central)', 'slug' => 'ca-central-1', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Canada West (Calgary)', 'slug' => 'ca-west-1', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Mexico (Central)', 'slug' => 'mx-central-1', 'flag' => '/flags/mx.svg'],
                ],
                'South America' => [
                    ['name' => 'South America (São Paulo)', 'slug' => 'sa-east-1', 'flag' => '/flags/br.svg'],
                ],
                'Europe' => [
                    ['name' => 'Europe (Ireland)', 'slug' => 'eu-west-1', 'flag' => '/flags/ie.svg'],
                    ['name' => 'Europe (London)', 'slug' => 'eu-west-2', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Europe (Paris)', 'slug' => 'eu-west-3', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Europe (Frankfurt)', 'slug' => 'eu-central-1', 'flag' => '/flags/de.svg'],
                    ['name' => 'Europe (Stockholm)', 'slug' => 'eu-north-1', 'flag' => '/flags/se.svg'],
                    ['name' => 'Europe (Milan)', 'slug' => 'eu-south-1', 'flag' => '/flags/it.svg'],
                    ['name' => 'Europe (Spain)', 'slug' => 'eu-south-2', 'flag' => '/flags/es.svg'],
                    ['name' => 'Europe (Zurich)', 'slug' => 'eu-central-2', 'flag' => '/flags/ch.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Asia Pacific (Mumbai)', 'slug' => 'ap-south-1', 'flag' => '/flags/in.svg'],
                    ['name' => 'Asia Pacific (Hyderabad)', 'slug' => 'ap-south-2', 'flag' => '/flags/in.svg'],
                    ['name' => 'Asia Pacific (Singapore)', 'slug' => 'ap-southeast-1', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Asia Pacific (Jakarta)', 'slug' => 'ap-southeast-3', 'flag' => '/flags/id.svg'],
                    ['name' => 'Asia Pacific (Sydney)', 'slug' => 'ap-southeast-2', 'flag' => '/flags/au.svg'],
                    ['name' => 'Asia Pacific (Melbourne)', 'slug' => 'ap-southeast-4', 'flag' => '/flags/au.svg'],
                    ['name' => 'Asia Pacific (Tokyo)', 'slug' => 'ap-northeast-1', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Asia Pacific (Osaka)', 'slug' => 'ap-northeast-3', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Asia Pacific (Seoul)', 'slug' => 'ap-northeast-2', 'flag' => '/flags/kr.svg'],
                    ['name' => 'Asia Pacific (Hong Kong)', 'slug' => 'ap-east-1', 'flag' => '/flags/hk.svg'],
                    ['name' => 'Asia Pacific (Taiwan)', 'slug' => 'ap-northeast-4', 'flag' => '/flags/tw.svg'],
                    ['name' => 'Asia Pacific (Thailand)', 'slug' => 'ap-southeast-5', 'flag' => '/flags/th.svg'],
                    ['name' => 'Asia Pacific (Malaysia)', 'slug' => 'ap-southeast-6', 'flag' => '/flags/my.svg'],
                ],
                'Middle East' => [
                    ['name' => 'Middle East (Bahrain)', 'slug' => 'me-south-1', 'flag' => '/flags/bh.svg'],
                    ['name' => 'Middle East (UAE)', 'slug' => 'me-central-1', 'flag' => '/flags/ae.svg'],
                    ['name' => 'Israel (Tel Aviv)', 'slug' => 'il-central-1', 'flag' => '/flags/il.svg'],
                ],
                'Africa' => [
                    ['name' => 'Africa (Cape Town)', 'slug' => 'af-south-1', 'flag' => '/flags/za.svg'],
                ],
            ],
            self::HETZNER => [
                'Europe' => [
                    ['name' => 'Falkenstein, Germany', 'slug' => 'fsn1', 'flag' => '/flags/de.svg'],
                    ['name' => 'Nuremberg, Germany', 'slug' => 'nbg1', 'flag' => '/flags/de.svg'],
                    ['name' => 'Helsinki, Finland', 'slug' => 'hel1', 'flag' => '/flags/fi.svg'],
                ],
                'North America' => [
                    ['name' => 'Ashburn, VA, USA', 'slug' => 'ash', 'flag' => '/flags/us.svg'],
                    ['name' => 'Hillsboro, OR, USA', 'slug' => 'hil', 'flag' => '/flags/us.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore', 'slug' => 'sin', 'flag' => '/flags/sg.svg'],
                ],
            ],
            self::DIGITAL_OCEAN => [
                'North America' => [
                    ['name' => 'New York 1', 'slug' => 'nyc1', 'flag' => '/flags/us.svg'],
                    ['name' => 'New York 2', 'slug' => 'nyc2', 'flag' => '/flags/us.svg'],
                    ['name' => 'New York 3', 'slug' => 'nyc3', 'flag' => '/flags/us.svg'],
                    ['name' => 'San Francisco 2', 'slug' => 'sfo2', 'flag' => '/flags/us.svg'],
                    ['name' => 'San Francisco 3', 'slug' => 'sfo3', 'flag' => '/flags/us.svg'],
                    ['name' => 'Toronto 1', 'slug' => 'tor1', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Atlanta 1', 'slug' => 'atl1', 'flag' => '/flags/us.svg'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam 3', 'slug' => 'ams3', 'flag' => '/flags/nl.svg'],
                    ['name' => 'London 1', 'slug' => 'lon1', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Frankfurt 1', 'slug' => 'fra1', 'flag' => '/flags/de.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore 1', 'slug' => 'sgp1', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Bangalore 1', 'slug' => 'blr1', 'flag' => '/flags/in.svg'],
                    ['name' => 'Sydney 1', 'slug' => 'syd1', 'flag' => '/flags/au.svg'],
                ],
            ],
            self::GOOGLE_CLOUD => [
                'North America' => [
                    ['name' => 'Iowa (us-central1)', 'slug' => 'us-central1', 'flag' => '/flags/us.svg'],
                    ['name' => 'South Carolina (us-east1)', 'slug' => 'us-east1', 'flag' => '/flags/us.svg'],
                    ['name' => 'N. Virginia (us-east4)', 'slug' => 'us-east4', 'flag' => '/flags/us.svg'],
                    ['name' => 'Columbus (us-east5)', 'slug' => 'us-east5', 'flag' => '/flags/us.svg'],
                    ['name' => 'Dallas (us-south1)', 'slug' => 'us-south1', 'flag' => '/flags/us.svg'],
                    ['name' => 'Oregon (us-west1)', 'slug' => 'us-west1', 'flag' => '/flags/us.svg'],
                    ['name' => 'Los Angeles (us-west2)', 'slug' => 'us-west2', 'flag' => '/flags/us.svg'],
                    ['name' => 'Salt Lake City (us-west3)', 'slug' => 'us-west3', 'flag' => '/flags/us.svg'],
                    ['name' => 'Las Vegas (us-west4)', 'slug' => 'us-west4', 'flag' => '/flags/us.svg'],
                    ['name' => 'Montreal (northamerica-northeast1)', 'slug' => 'northamerica-northeast1', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Toronto (northamerica-northeast2)', 'slug' => 'northamerica-northeast2', 'flag' => '/flags/ca.svg'],
                ],
                'South America' => [
                    ['name' => 'São Paulo (southamerica-east1)', 'slug' => 'southamerica-east1', 'flag' => '/flags/br.svg'],
                    ['name' => 'Santiago (southamerica-west1)', 'slug' => 'southamerica-west1', 'flag' => '/flags/cl.svg'],
                ],
                'Europe' => [
                    ['name' => 'Belgium (europe-west1)', 'slug' => 'europe-west1', 'flag' => '/flags/be.svg'],
                    ['name' => 'London (europe-west2)', 'slug' => 'europe-west2', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Frankfurt (europe-west3)', 'slug' => 'europe-west3', 'flag' => '/flags/de.svg'],
                    ['name' => 'Netherlands (europe-west4)', 'slug' => 'europe-west4', 'flag' => '/flags/nl.svg'],
                    ['name' => 'Zurich (europe-west6)', 'slug' => 'europe-west6', 'flag' => '/flags/ch.svg'],
                    ['name' => 'Milan (europe-west8)', 'slug' => 'europe-west8', 'flag' => '/flags/it.svg'],
                    ['name' => 'Paris (europe-west9)', 'slug' => 'europe-west9', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Berlin (europe-west10)', 'slug' => 'europe-west10', 'flag' => '/flags/de.svg'],
                    ['name' => 'Turin (europe-west12)', 'slug' => 'europe-west12', 'flag' => '/flags/it.svg'],
                    ['name' => 'Finland (europe-north1)', 'slug' => 'europe-north1', 'flag' => '/flags/fi.svg'],
                    ['name' => 'Warsaw (europe-central2)', 'slug' => 'europe-central2', 'flag' => '/flags/pl.svg'],
                    ['name' => 'Madrid (europe-southwest1)', 'slug' => 'europe-southwest1', 'flag' => '/flags/es.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Mumbai (asia-south1)', 'slug' => 'asia-south1', 'flag' => '/flags/in.svg'],
                    ['name' => 'Delhi (asia-south2)', 'slug' => 'asia-south2', 'flag' => '/flags/in.svg'],
                    ['name' => 'Singapore (asia-southeast1)', 'slug' => 'asia-southeast1', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Jakarta (asia-southeast2)', 'slug' => 'asia-southeast2', 'flag' => '/flags/id.svg'],
                    ['name' => 'Hong Kong (asia-east2)', 'slug' => 'asia-east2', 'flag' => '/flags/hk.svg'],
                    ['name' => 'Taiwan (asia-east1)', 'slug' => 'asia-east1', 'flag' => '/flags/tw.svg'],
                    ['name' => 'Tokyo (asia-northeast1)', 'slug' => 'asia-northeast1', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Osaka (asia-northeast2)', 'slug' => 'asia-northeast2', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Seoul (asia-northeast3)', 'slug' => 'asia-northeast3', 'flag' => '/flags/kr.svg'],
                    ['name' => 'Sydney (australia-southeast1)', 'slug' => 'australia-southeast1', 'flag' => '/flags/au.svg'],
                    ['name' => 'Melbourne (australia-southeast2)', 'slug' => 'australia-southeast2', 'flag' => '/flags/au.svg'],
                ],
                'Middle East' => [
                    ['name' => 'Tel Aviv (me-west1)', 'slug' => 'me-west1', 'flag' => '/flags/il.svg'],
                    ['name' => 'Doha (me-central1)', 'slug' => 'me-central1', 'flag' => '/flags/qa.svg'],
                    ['name' => 'Dammam (me-central2)', 'slug' => 'me-central2', 'flag' => '/flags/sa.svg'],
                ],
                'Africa' => [
                    ['name' => 'Johannesburg (africa-south1)', 'slug' => 'africa-south1', 'flag' => '/flags/za.svg'],
                ],
            ],
            self::VULTR => [
                'North America' => [
                    ['name' => 'Atlanta, GA', 'slug' => 'atl', 'flag' => '/flags/us.svg'],
                    ['name' => 'Chicago, IL', 'slug' => 'ord', 'flag' => '/flags/us.svg'],
                    ['name' => 'Dallas, TX', 'slug' => 'dfw', 'flag' => '/flags/us.svg'],
                    ['name' => 'Honolulu, HI', 'slug' => 'hnl', 'flag' => '/flags/us.svg'],
                    ['name' => 'Los Angeles, CA', 'slug' => 'lax', 'flag' => '/flags/us.svg'],
                    ['name' => 'Miami, FL', 'slug' => 'mia', 'flag' => '/flags/us.svg'],
                    ['name' => 'New York Area', 'slug' => 'ewr', 'flag' => '/flags/us.svg'],
                    ['name' => 'San Francisco Bay Area, CA', 'slug' => 'sjc', 'flag' => '/flags/us.svg'],
                    ['name' => 'Seattle, WA', 'slug' => 'sea', 'flag' => '/flags/us.svg'],
                    ['name' => 'Toronto, Canada', 'slug' => 'yto', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Mexico City, Mexico', 'slug' => 'mex', 'flag' => '/flags/mx.svg'],
                ],
                'South America' => [
                    ['name' => 'São Paulo, Brazil', 'slug' => 'sao', 'flag' => '/flags/br.svg'],
                    ['name' => 'Santiago, Chile', 'slug' => 'scl', 'flag' => '/flags/cl.svg'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'ams', 'flag' => '/flags/nl.svg'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'fra', 'flag' => '/flags/de.svg'],
                    ['name' => 'London, United Kingdom', 'slug' => 'lhr', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Madrid, Spain', 'slug' => 'mad', 'flag' => '/flags/es.svg'],
                    ['name' => 'Manchester, United Kingdom', 'slug' => 'man', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Paris, France', 'slug' => 'cdg', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Stockholm, Sweden', 'slug' => 'arn', 'flag' => '/flags/se.svg'],
                    ['name' => 'Warsaw, Poland', 'slug' => 'waw', 'flag' => '/flags/pl.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Tokyo, Japan', 'slug' => 'nrt', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Osaka, Japan', 'slug' => 'itm', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Seoul, South Korea', 'slug' => 'icn', 'flag' => '/flags/kr.svg'],
                    ['name' => 'Singapore', 'slug' => 'sgp', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Mumbai, India', 'slug' => 'bom', 'flag' => '/flags/in.svg'],
                    ['name' => 'Delhi NCR, India', 'slug' => 'del', 'flag' => '/flags/in.svg'],
                    ['name' => 'Bangalore, India', 'slug' => 'blr', 'flag' => '/flags/in.svg'],
                    ['name' => 'Sydney, Australia', 'slug' => 'syd', 'flag' => '/flags/au.svg'],
                    ['name' => 'Melbourne, Australia', 'slug' => 'mel', 'flag' => '/flags/au.svg'],
                ],
                'Middle East' => [
                    ['name' => 'Tel Aviv-Yafo, Israel', 'slug' => 'tlv', 'flag' => '/flags/il.svg'],
                ],
                'Africa' => [
                    ['name' => 'Johannesburg, South Africa', 'slug' => 'jnb', 'flag' => '/flags/za.svg'],
                ],
            ],
            self::LINODE => [
                'North America' => [
                    ['name' => 'Newark, NJ', 'slug' => 'us-east', 'flag' => '/flags/us.svg'],
                    ['name' => 'Atlanta, GA', 'slug' => 'us-southeast', 'flag' => '/flags/us.svg'],
                    ['name' => 'Dallas, TX', 'slug' => 'us-central', 'flag' => '/flags/us.svg'],
                    ['name' => 'Fremont, CA', 'slug' => 'us-west', 'flag' => '/flags/us.svg'],
                    ['name' => 'Chicago, IL', 'slug' => 'us-ord', 'flag' => '/flags/us.svg'],
                    ['name' => 'Los Angeles, CA', 'slug' => 'us-lax', 'flag' => '/flags/us.svg'],
                    ['name' => 'Miami, FL', 'slug' => 'us-mia', 'flag' => '/flags/us.svg'],
                    ['name' => 'Seattle, WA', 'slug' => 'us-sea', 'flag' => '/flags/us.svg'],
                    ['name' => 'Washington, D.C.', 'slug' => 'us-iad', 'flag' => '/flags/us.svg'],
                    ['name' => 'Toronto, Canada', 'slug' => 'ca-central', 'flag' => '/flags/ca.svg'],
                ],
                'South America' => [
                    ['name' => 'São Paulo, Brazil', 'slug' => 'br-gru', 'flag' => '/flags/br.svg'],
                ],
                'Europe' => [
                    ['name' => 'London, UK', 'slug' => 'eu-west', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'eu-central', 'flag' => '/flags/de.svg'],
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'nl-ams', 'flag' => '/flags/nl.svg'],
                    ['name' => 'Stockholm, Sweden', 'slug' => 'se-sto', 'flag' => '/flags/se.svg'],
                    ['name' => 'Paris, France', 'slug' => 'fr-par', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Milan, Italy', 'slug' => 'it-mil', 'flag' => '/flags/it.svg'],
                    ['name' => 'Madrid, Spain', 'slug' => 'es-mad', 'flag' => '/flags/es.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Mumbai, India', 'slug' => 'ap-west', 'flag' => '/flags/in.svg'],
                    ['name' => 'Chennai, India', 'slug' => 'in-maa', 'flag' => '/flags/in.svg'],
                    ['name' => 'Singapore', 'slug' => 'ap-south', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Tokyo, Japan', 'slug' => 'ap-northeast', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Osaka, Japan', 'slug' => 'jp-osa', 'flag' => '/flags/jp.svg'],
                    ['name' => 'Jakarta, Indonesia', 'slug' => 'id-cgk', 'flag' => '/flags/id.svg'],
                    ['name' => 'Sydney, Australia', 'slug' => 'ap-southeast', 'flag' => '/flags/au.svg'],
                    ['name' => 'Melbourne, Australia', 'slug' => 'au-mel', 'flag' => '/flags/au.svg'],
                ],
            ],
            self::LEASEWEB => [
                'North America' => [
                    ['name' => 'Washington, D.C., USA', 'slug' => 'wdc-02', 'flag' => '/flags/us.svg'],
                    ['name' => 'San Francisco, CA, USA', 'slug' => 'sfo-01', 'flag' => '/flags/us.svg'],
                    ['name' => 'Montreal, Canada', 'slug' => 'yul-01', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Miami, FL, USA', 'slug' => 'mia-01', 'flag' => '/flags/us.svg'],
                ],
                'Europe' => [
                    ['name' => 'Amsterdam, Netherlands', 'slug' => 'ams-01', 'flag' => '/flags/nl.svg'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'fra-01', 'flag' => '/flags/de.svg'],
                    ['name' => 'London, United Kingdom', 'slug' => 'lon-01', 'flag' => '/flags/gb.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore', 'slug' => 'sin-01', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Tokyo, Japan', 'slug' => 'tyo-10', 'flag' => '/flags/jp.svg'],
                ],
            ],
            self::OVH => [
                'North America' => [
                    ['name' => 'Beauharnois, Canada', 'slug' => 'bhs', 'flag' => '/flags/ca.svg'],
                    ['name' => 'Hillsboro, USA', 'slug' => 'us-west-or-1', 'flag' => '/flags/us.svg'],
                    ['name' => 'Vint Hill, USA', 'slug' => 'us-east-va-1', 'flag' => '/flags/us.svg'],
                ],
                'Europe' => [
                    ['name' => 'Gravelines, France', 'slug' => 'gra', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Strasbourg, France', 'slug' => 'sbg', 'flag' => '/flags/fr.svg'],
                    ['name' => 'Frankfurt, Germany', 'slug' => 'de', 'flag' => '/flags/de.svg'],
                    ['name' => 'London, United Kingdom', 'slug' => 'uk', 'flag' => '/flags/gb.svg'],
                    ['name' => 'Warsaw, Poland', 'slug' => 'waw', 'flag' => '/flags/pl.svg'],
                ],
                'Asia Pacific' => [
                    ['name' => 'Singapore', 'slug' => 'sgp', 'flag' => '/flags/sg.svg'],
                    ['name' => 'Sydney, Australia', 'slug' => 'syd', 'flag' => '/flags/au.svg'],
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
     * Get all valid region slugs for this cloud provider type.
     * Returns an array of region slug strings for validation purposes.
     */
    public function getValidRegionSlugs(): array
    {
        return collect($this->flatRegions())->pluck('slug')->toArray();
    }

    /**
     * Get supported server types for this cloud provider.
     * Returns an array of server type configurations with specifications.
     */
    public function serverTypes(): array
    {
        return match ($this) {
            self::HETZNER => [
                'cx22' => ['cpu' => 2, 'ram' => 4, 'disk' => 40, 'name' => 'CX22 - 2 vCPU, 4GB RAM, 40GB SSD'],
                'cx32' => ['cpu' => 4, 'ram' => 8, 'disk' => 80, 'name' => 'CX32 - 4 vCPU, 8GB RAM, 80GB SSD'],
                'cx42' => ['cpu' => 8, 'ram' => 16, 'disk' => 160, 'name' => 'CX42 - 8 vCPU, 16GB RAM, 160GB SSD'],
                'cx52' => ['cpu' => 16, 'ram' => 32, 'disk' => 320, 'name' => 'CX52 - 16 vCPU, 32GB RAM, 320GB SSD'],
                'cpx21' => ['cpu' => 3, 'ram' => 4, 'disk' => 80, 'name' => 'CPX21 - 3 vCPU, 4GB RAM, 80GB SSD'],
                'cpx31' => ['cpu' => 4, 'ram' => 8, 'disk' => 160, 'name' => 'CPX31 - 4 vCPU, 8GB RAM, 160GB SSD'],
                'cpx41' => ['cpu' => 8, 'ram' => 16, 'disk' => 240, 'name' => 'CPX41 - 8 vCPU, 16GB RAM, 240GB SSD'],
                'cpx51' => ['cpu' => 16, 'ram' => 32, 'disk' => 360, 'name' => 'CPX51 - 16 vCPU, 32GB RAM, 360GB SSD'],
            ],
            self::AWS => [
                't3.medium' => ['cpu' => 2, 'ram' => 4, 'disk' => 20, 'name' => 't3.medium - 2 vCPU, 4GB RAM, 20GB EBS'],
                't3.large' => ['cpu' => 2, 'ram' => 8, 'disk' => 20, 'name' => 't3.large - 2 vCPU, 8GB RAM, 20GB EBS'],
                't3.xlarge' => ['cpu' => 4, 'ram' => 16, 'disk' => 20, 'name' => 't3.xlarge - 4 vCPU, 16GB RAM, 20GB EBS'],
                't3.2xlarge' => ['cpu' => 8, 'ram' => 32, 'disk' => 20, 'name' => 't3.2xlarge - 8 vCPU, 32GB RAM, 20GB EBS'],
                'm5.large' => ['cpu' => 2, 'ram' => 8, 'disk' => 20, 'name' => 'm5.large - 2 vCPU, 8GB RAM, 20GB EBS'],
                'm5.xlarge' => ['cpu' => 4, 'ram' => 16, 'disk' => 20, 'name' => 'm5.xlarge - 4 vCPU, 16GB RAM, 20GB EBS'],
                'm5.2xlarge' => ['cpu' => 8, 'ram' => 32, 'disk' => 20, 'name' => 'm5.2xlarge - 8 vCPU, 32GB RAM, 20GB EBS'],
                'm5.4xlarge' => ['cpu' => 16, 'ram' => 64, 'disk' => 20, 'name' => 'm5.4xlarge - 16 vCPU, 64GB RAM, 20GB EBS'],
            ],
            self::DIGITAL_OCEAN => [
                's-2vcpu-2gb' => ['cpu' => 2, 'ram' => 2, 'disk' => 50, 'name' => 'Basic - 2 vCPU, 2GB RAM, 50GB SSD'],
                's-2vcpu-4gb' => ['cpu' => 2, 'ram' => 4, 'disk' => 80, 'name' => 'Basic - 2 vCPU, 4GB RAM, 80GB SSD'],
                's-4vcpu-8gb' => ['cpu' => 4, 'ram' => 8, 'disk' => 160, 'name' => 'Basic - 4 vCPU, 8GB RAM, 160GB SSD'],
                's-8vcpu-16gb' => ['cpu' => 8, 'ram' => 16, 'disk' => 320, 'name' => 'Basic - 8 vCPU, 16GB RAM, 320GB SSD'],
                'c-4' => ['cpu' => 4, 'ram' => 8, 'disk' => 50, 'name' => 'CPU-Optimized - 4 vCPU, 8GB RAM, 50GB SSD'],
                'c-8' => ['cpu' => 8, 'ram' => 16, 'disk' => 100, 'name' => 'CPU-Optimized - 8 vCPU, 16GB RAM, 100GB SSD'],
            ],
            default => [],
        };
    }

    /**
     * Get valid server type slugs for this cloud provider.
     * Returns an array of server type identifiers for validation.
     */
    public function getValidServerTypes(): array
    {
        return array_keys($this->serverTypes());
    }

    /**
     * Get server specifications for a specific server type.
     * Returns CPU, RAM, and disk specifications or null if type not found.
     */
    public function getServerSpecs(string $serverType): ?array
    {
        return $this->serverTypes()[$serverType] ?? null;
    }

    /**
     * Get all cloud provider data for frontend consumption.
     * Returns an array with provider information including type, name, implementation status, description, documentation link, credential fields, and country code.
     */
    public static function allProviders(): array
    {
        $implementedTypes = collect(self::implemented())->pluck('value')->toArray();

        return collect(self::cases())->map(fn ($case) => [
            'type' => $case->value,
            'name' => $case->label(),
            'implemented' => in_array($case->value, $implementedTypes),
            'description' => $case->description(),
            'documentationLink' => $case->documentationLink(),
            'credentialFields' => $case->credentialFields(),
        ])->toArray();
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

    /**
     * Get all cloud provider server types data for frontend consumption.
     * Returns an associative array with provider types as keys and their server types with specifications.
     */
    public static function allServerTypes(): array
    {
        $serverTypes = [];
        foreach (self::cases() as $case) {
            $serverTypes[$case->value] = $case->serverTypes();
        }

        return $serverTypes;
    }
}
