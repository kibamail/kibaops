import { RegionSelector } from '@/Components/CloudProviders/RegionSelector';
import { AWSIcon } from '@/Components/Icons/aws.svg';
import { DigitalOceanIcon } from '@/Components/Icons/digital-ocean.svg';
import { GoogleCloudIcon } from '@/Components/Icons/google-cloud.svg';
import { HetznerIcon } from '@/Components/Icons/hetzner.svg';
import { LeaseWebIcon } from '@/Components/Icons/leaseweb.svg';
import { LinodeIcon } from '@/Components/Icons/linode.svg';
import { OVHIcon } from '@/Components/Icons/ovh.svg';
import { VultrIcon } from '@/Components/Icons/vultr.svg';
import * as NumberField from '@/Components/NumberField';
import type { CloudProviderType, PageProps } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { Button } from '@kibamail/owly/button';
import { Checkbox } from '@kibamail/owly/checkbox';
import * as Dialog from '@kibamail/owly/dialog';
import { InputError, InputHint } from '@kibamail/owly/input-hint';
import { InputLabel } from '@kibamail/owly/input-label';
import * as Select from '@kibamail/owly/select-field';
import { Text } from '@kibamail/owly/text';
import * as TextField from '@kibamail/owly/text-field';
import { VisuallyHidden } from '@radix-ui/react-visually-hidden';

interface CreateClusterModalProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
}

const providerIcons: Record<CloudProviderType, React.ComponentType<{ className?: string }>> = {
  aws: AWSIcon,
  hetzner: HetznerIcon,
  leaseweb: LeaseWebIcon,
  google_cloud: GoogleCloudIcon,
  digital_ocean: DigitalOceanIcon,
  linode: LinodeIcon,
  vultr: VultrIcon,
  ovh: OVHIcon,
};

export function CreateClusterModal({ isOpen, onOpenChange }: CreateClusterModalProps) {
  const { workspaceCloudProviders, cloudProviderServerTypes } = usePage<PageProps>().props;

  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    cloud_provider_id: workspaceCloudProviders.length === 1 ? workspaceCloudProviders[0].id : '',
    region: '',
    worker_nodes_count: 3,
    storage_nodes_count: 3,
    shared_storage_worker_nodes: false as boolean,
    server_type: '',
  });

  const selectedProvider = workspaceCloudProviders.find((p) => p.id === data.cloud_provider_id);
  const availableServerTypes = selectedProvider
    ? Object.entries(cloudProviderServerTypes[selectedProvider.type] || {}).map(
        ([slug, specs]) => ({
          slug,
          ...specs,
        })
      )
    : [];

  function onCloudProviderChange(providerId: string) {
    setData((data) => ({
      ...data,
      cloud_provider_id: providerId,
      region: '',
      server_type: '',
    }));
  }

  function onRegionChange(region: string) {
    setData('region', region);
  }

  function onSharedStorageChange(checked: boolean) {
    setData('shared_storage_worker_nodes', checked);
    setData('storage_nodes_count', checked ? 0 : 3);
  }

  function onServerTypeChange(serverType: string) {
    setData('server_type', serverType);
  }

  function onSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    post(route('clusters.store'), {
      onSuccess: () => {
        reset();
        onOpenChange(false);
      },
    });
  }

  return (
    <Dialog.Root open={isOpen} onOpenChange={onOpenChange}>
      <Dialog.Content>
        <Dialog.Header>
          <Dialog.Title>Create New Cluster</Dialog.Title>
          <VisuallyHidden>
            <Dialog.Description>
              Set up a new Nomad cluster to run your applications
            </Dialog.Description>
          </VisuallyHidden>
        </Dialog.Header>

        <div className="px-5 pt-2 pb-4">
          <Text className="kb-content-secondary text-sm leading-relaxed">
            Set up a new Nomad cluster to run your applications. Configure your cluster
            specifications and deployment settings.
          </Text>
        </div>

        <form onSubmit={onSubmit}>
          <div className="px-5 pb-5 space-y-4">
            <TextField.Root
              name="name"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
              placeholder="Enter cluster name"
              required
            >
              <TextField.Label>Cluster Name</TextField.Label>
              {errors.name && <TextField.Error>{errors.name}</TextField.Error>}
            </TextField.Root>

            <Select.Root
              name="cloud_provider_id"
              value={data.cloud_provider_id}
              onValueChange={onCloudProviderChange}
              disabled={workspaceCloudProviders.length === 0}
            >
              <Select.Label>Cloud Provider</Select.Label>
              <Select.Trigger
                placeholder={
                  workspaceCloudProviders.length === 0
                    ? 'No cloud providers available'
                    : 'Select a cloud provider'
                }
              />
              <Select.Content className="z-100">
                {workspaceCloudProviders.map((provider) => {
                  const IconComponent = providerIcons[provider.type];
                  return (
                    <Select.Item key={provider.id} value={provider.id}>
                      <div className="flex items-center gap-2">
                        <IconComponent className="w-4 h-4" />
                        <span>{provider.name}</span>
                      </div>
                    </Select.Item>
                  );
                })}
              </Select.Content>
              {errors.cloud_provider_id && <Select.Error>{errors.cloud_provider_id}</Select.Error>}
            </Select.Root>

            {selectedProvider && (
              <div>
                <InputLabel htmlFor="region-input">Region</InputLabel>
                <RegionSelector
                  providerType={selectedProvider.type}
                  selectedRegion={data.region}
                  onRegionChange={onRegionChange}
                  placeholder="Select a region"
                />
                {errors.region && <InputError baseId="region-input">{errors.region}</InputError>}
                <input type="hidden" id="region-input" name="region" value={data.region} />
              </div>
            )}

            <NumberField.Root
              name="worker_nodes_count"
              value={data.worker_nodes_count}
              min={3}
              max={50}
              onChange={(value: number) => setData('worker_nodes_count', value)}
            >
              <NumberField.Label>Worker Nodes</NumberField.Label>
              <NumberField.Field placeholder="Enter number of worker nodes">
                <NumberField.DecrementButton />
                <NumberField.IncrementButton />
                <NumberField.Hint>Minimum 3 worker nodes required</NumberField.Hint>
                {errors.worker_nodes_count && (
                  <NumberField.Error>{errors.worker_nodes_count}</NumberField.Error>
                )}
              </NumberField.Field>
            </NumberField.Root>

            <div>
              <div className="flex items-center gap-3 mb-1">
                <Checkbox
                  id="shared_storage_worker_nodes"
                  name="shared_storage_worker_nodes"
                  checked={data.shared_storage_worker_nodes}
                  onCheckedChange={(checked) => onSharedStorageChange(checked === true)}
                />
                <InputLabel htmlFor="shared_storage_worker_nodes">
                  Shared storage and worker nodes
                </InputLabel>
              </div>
              <Text size="sm" className="kb-content-tertiary leading-0.5">
                When enabled, nodes will handle both storage and worker roles, eliminating the need
                for separate storage nodes. Only enable this if you are optimising for cost.
              </Text>
            </div>

            <div className={data.shared_storage_worker_nodes ? 'opacity-50' : ''}>
              <NumberField.Root
                name="storage_nodes_count"
                value={data.storage_nodes_count}
                min={3}
                max={50}
                disabled={data.shared_storage_worker_nodes}
                onChange={(value: number) => setData('storage_nodes_count', value)}
              >
                <NumberField.Label>Storage Nodes</NumberField.Label>
                <NumberField.Field placeholder="Enter number of storage nodes">
                  <NumberField.DecrementButton />
                  <NumberField.IncrementButton />
                  <NumberField.Hint>Minimum 3 storage nodes required</NumberField.Hint>
                  {errors.storage_nodes_count && (
                    <NumberField.Error>{errors.storage_nodes_count}</NumberField.Error>
                  )}
                </NumberField.Field>
              </NumberField.Root>
            </div>

            {selectedProvider && availableServerTypes.length > 0 && (
              <Select.Root
                name="server_type"
                value={data.server_type}
                onValueChange={onServerTypeChange}
              >
                <Select.Label>Node type</Select.Label>
                <Select.Trigger placeholder="Select node type" />
                <Select.Content className="z-100">
                  {availableServerTypes.map((serverType) => (
                    <Select.Item key={serverType.slug} value={serverType.slug}>
                      <div className="flex flex-col">
                        <span className="font-medium">{serverType.name}</span>
                      </div>
                    </Select.Item>
                  ))}
                </Select.Content>
                {errors.server_type && <Select.Error>{errors.server_type}</Select.Error>}
              </Select.Root>
            )}
          </div>

          <Dialog.Footer className="flex justify-between">
            <Dialog.Close asChild disabled={processing}>
              <Button variant="secondary">Cancel</Button>
            </Dialog.Close>
            <Button type="submit" loading={processing}>
              Create Cluster
            </Button>
          </Dialog.Footer>
        </form>
      </Dialog.Content>
    </Dialog.Root>
  );
}
