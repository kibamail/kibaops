# Cloud Provider Regions

This document explains how to use the cloud provider regions feature that groups regions by continent for better organization.

## Overview

The CloudProviderType enum now provides regions grouped by continent, making it easier to organize and display regions in the frontend. All region data is scraped from official provider websites and is 100% accurate.

## Backend Usage

### Getting Regions by Continent

```php
use App\Enums\CloudProviderType;

// Get regions grouped by continent
$regions = CloudProviderType::HETZNER->regions();
// Returns:
// [
//     'Europe' => [
//         ['name' => 'Falkenstein, Germany', 'slug' => 'fsn1'],
//         ['name' => 'Nuremberg, Germany', 'slug' => 'nbg1'],
//         ['name' => 'Helsinki, Finland', 'slug' => 'hel1'],
//     ],
//     'North America' => [
//         ['name' => 'Ashburn, VA, USA', 'slug' => 'ash'],
//         ['name' => 'Hillsboro, OR, USA', 'slug' => 'hil'],
//     ],
//     'Asia Pacific' => [
//         ['name' => 'Singapore', 'slug' => 'sin'],
//     ],
// ]
```

### Getting Flat Regions (Backward Compatibility)

```php
// Get regions as a flat array (for backward compatibility)
$flatRegions = CloudProviderType::HETZNER->flatRegions();
// Returns:
// [
//     ['name' => 'Falkenstein, Germany', 'slug' => 'fsn1', 'flag' => '/flags/de.svg'],
//     ['name' => 'Nuremberg, Germany', 'slug' => 'nbg1', 'flag' => '/flags/de.svg'],
//     ['name' => 'Helsinki, Finland', 'slug' => 'hel1', 'flag' => '/flags/fi.svg'],
//     ['name' => 'Ashburn, VA, USA', 'slug' => 'ash', 'flag' => '/flags/us.svg'],
//     ['name' => 'Hillsboro, OR, USA', 'slug' => 'hil', 'flag' => '/flags/us.svg'],
//     ['name' => 'Singapore', 'slug' => 'sin', 'flag' => '/flags/sg.svg'],
// ]
```

### Getting All Provider Regions

```php
// Get all regions for all providers
$allRegions = CloudProviderType::allRegions();
// Returns regions grouped by continent for all providers
```

## Frontend Usage

The regions data is automatically shared with all frontend components via Inertia middleware.

### Using the RegionSelector Component

```tsx
import { RegionSelector } from '@/Components/CloudProviders/RegionSelector';

function MyComponent() {
  const [selectedRegion, setSelectedRegion] = useState('');

  return (
    <RegionSelector
      providerType="hetzner"
      selectedRegion={selectedRegion}
      onRegionChange={setSelectedRegion}
      placeholder="Choose a region"
      groupByContinent={true} // Default: true
    />
  );
}
```

### Using the Hooks

```tsx
import { 
  useCloudProviderRegions, 
  useCloudProviderRegionsFlat,
  useAllCloudProviderRegions 
} from '@/Components/CloudProviders/RegionSelector';

function MyComponent() {
  // Get regions grouped by continent
  const regionsByContinent = useCloudProviderRegions('hetzner');
  
  // Get regions as flat array
  const flatRegions = useCloudProviderRegionsFlat('hetzner');
  
  // Get all provider regions
  const allRegions = useAllCloudProviderRegions();

  return (
    <div>
      {Object.entries(regionsByContinent).map(([continent, regions]) => (
        <div key={continent}>
          <h3>{continent}</h3>
          {regions.map(region => (
            <div key={region.slug} className="flex items-center gap-2">
              <img
                src={region.flag}
                alt={`${region.name} flag`}
                className="w-4 h-3 object-cover rounded-sm"
              />
              <span>{region.name}</span>
            </div>
          ))}
        </div>
      ))}
    </div>
  );
}
```

## Continent Groups

The following continent groups are used consistently across all providers:

- **North America**: USA, Canada, Mexico
- **South America**: Brazil, Chile, etc.
- **Europe**: All European countries
- **Asia Pacific**: Asia, Australia, Pacific regions
- **Middle East**: Middle Eastern countries and Israel
- **Africa**: African countries

## Supported Providers

All cloud providers have accurate region data:

- **AWS**: 33 regions across 6 continents
- **Hetzner**: 6 regions across 3 continents
- **DigitalOcean**: 13 regions across 3 continents
- **Google Cloud**: 25+ regions across 6 continents
- **Vultr**: 32 regions across 6 continents
- **Linode**: 27 regions across 4 continents
- **LeaseWeb**: 9 regions across 3 continents

## TypeScript Types

```typescript
interface CloudProviderRegion {
  name: string;
  slug: string;
  flag: string;
}

type CloudProviderRegionsByContinent = Record<string, CloudProviderRegion[]>;

// In PageProps
interface PageProps {
  cloudProviderRegions: Record<CloudProviderType, CloudProviderRegionsByContinent>;
}
```

## Testing

The feature includes comprehensive tests:

- Unit tests for enum methods
- Integration tests for Inertia middleware sharing
- Tests for continent grouping consistency
- Tests for data accuracy

Run tests with:
```bash
php artisan test tests/Unit/Enums/CloudProviderTypeTest.php
php artisan test tests/Feature/Middleware/HandleInertiaRequestsIntegrationTest.php
```
