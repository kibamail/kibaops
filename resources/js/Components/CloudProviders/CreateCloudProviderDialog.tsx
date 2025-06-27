import type { CloudProviderType, PageProps } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { Button } from '@kibamail/owly/button';
import * as Dialog from '@kibamail/owly/dialog';
import * as Select from '@kibamail/owly/select-field';
import { Text } from '@kibamail/owly/text';
import * as TextField from '@kibamail/owly/text-field';
import { VisuallyHidden } from '@radix-ui/react-visually-hidden';
import { type FormEventHandler, useEffect, useState } from 'react';

// Import cloud provider icons
import { AWSIcon } from '@/Components/Icons/aws.svg';
import { DigitalOceanIcon } from '@/Components/Icons/digital-ocean.svg';
import { GoogleCloudIcon } from '@/Components/Icons/google-cloud.svg';
import { HetznerIcon } from '@/Components/Icons/hetzner.svg';
import { LeaseWebIcon } from '@/Components/Icons/leaseweb.svg';
import { LinodeIcon } from '@/Components/Icons/linode.svg';
import { OVHIcon } from '@/Components/Icons/ovh.svg';
import { VultrIcon } from '@/Components/Icons/vultr.svg';

interface CreateCloudProviderDialogProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  workspaceId: string;
  preselectedProviderType?: CloudProviderType;
}

// Icon mapping for cloud providers
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

export function CreateCloudProviderDialog({
  isOpen,
  onOpenChange,
  workspaceId,
  preselectedProviderType,
}: CreateCloudProviderDialogProps) {
  const { cloudProviders } = usePage<PageProps>().props;
  const [selectedProviderType, setSelectedProviderType] = useState<CloudProviderType | ''>('');

  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    type: '' as CloudProviderType | '',
    credentials: [] as string[],
  });

  const selectedProvider = cloudProviders.find(
    (provider) => provider.type === selectedProviderType
  );

  useEffect(() => {
    if (isOpen && preselectedProviderType) {
      setSelectedProviderType(preselectedProviderType);
      setData('type', preselectedProviderType);

      const provider = cloudProviders.find((provider) => provider.type === preselectedProviderType);
      if (provider) {
        const emptyCredentials = new Array(provider.credentialFields.length).fill('');
        setData('credentials', emptyCredentials);
      }
    } else if (!isOpen) {
      reset();
      setSelectedProviderType('');
    }
  }, [isOpen, preselectedProviderType, cloudProviders, setData, reset]);

  const handleProviderTypeChange = (value: string) => {
    const providerType = value as CloudProviderType;
    setSelectedProviderType(providerType);
    setData('type', providerType);

    const provider = cloudProviders.find((provider) => provider.type === providerType);
    if (provider) {
      const emptyCredentials = new Array(provider.credentialFields.length).fill('');
      setData('credentials', emptyCredentials);
    }
  };

  const handleCredentialChange = (index: number, value: string) => {
    const newCredentials = [...data.credentials];
    newCredentials[index] = value;
    setData('credentials', newCredentials);
  };

  const submit: FormEventHandler = (event) => {
    event.preventDefault();

    post(route('workspaces.cloud-providers.store', workspaceId), {
      onSuccess() {
        onOpenChange(false);
      },
    });
  };

  const sortedProviders = [...cloudProviders].sort((a, b) => {
    if (a.implemented && !b.implemented) return -1;
    if (!a.implemented && b.implemented) return 1;
    return 0;
  });

  return (
    <Dialog.Root open={isOpen} onOpenChange={onOpenChange}>
      <Dialog.Content>
        <Dialog.Header>
          <Dialog.Title>Connect a cloud provider</Dialog.Title>
          <VisuallyHidden>
            <Dialog.Description>Connect a new cloud provider to your workspace</Dialog.Description>
          </VisuallyHidden>
        </Dialog.Header>

        <div className="px-5 pt-2 pb-4">
          <Text className="kb-content-secondary text-sm leading-relaxed">
            Connect your cloud provider to start provisioning infrastructure. Your credentials are
            securely stored and encrypted. You can connect multiple cloud providers to a single
            workspace.
          </Text>
        </div>

        <form onSubmit={submit} autoComplete="off">
          <div className="px-5 pb-5 space-y-4">
            {/* Cloud Provider Selection */}
            <Select.Root name="type" value={data.type} onValueChange={handleProviderTypeChange}>
              <Select.Label>Cloud provider</Select.Label>
              <Select.Trigger placeholder="Select a cloud provider" />
              <Select.Content className="z-50 relative w-full">
                {sortedProviders.map((provider) => {
                  const IconComponent = providerIcons[provider.type];

                  return (
                    <Select.Item
                      key={provider.type}
                      value={provider.type}
                      disabled={!provider.implemented}
                      className="w-full"
                    >
                      <div className="flex items-center justify-between w-full">
                        <div className="flex items-center gap-2">
                          <IconComponent className="w-4 h-4" />
                          <span>{provider.name}</span>
                        </div>
                        {!provider.implemented && (
                          <span className="text-xs px-2 py-0.5 lowercase kb-content-disabled font-bold kb-background-hover border kb-border-tertiary rounded-full">
                            Coming Soon
                          </span>
                        )}
                      </div>
                    </Select.Item>
                  );
                })}
              </Select.Content>
              {errors.type && <Select.Error>{errors.type}</Select.Error>}
            </Select.Root>

            {/* Provider Name */}
            <TextField.Root
              required
              name="name"
              value={data.name}
              autoComplete="off"
              data-form-type="other"
              data-lpignore="true"
              onChange={(e) => setData('name', e.target.value)}
              placeholder="e.g. production aws"
            >
              <TextField.Label>Cloud provider name</TextField.Label>
              {errors.name && <TextField.Error>{errors.name}</TextField.Error>}
            </TextField.Root>

            {/* Dynamic Credential Fields */}
            {selectedProvider && (
              <div className="space-y-4">
                <div className="border-t kb-border-tertiary pt-4">
                  <div className="mb-6 flex flex-col">
                    <Text size="lg" className="font-semibold mb-1">
                      Credentials
                    </Text>

                    <div className="space-y-2">
                      <Text className="kb-content-tertiary">{selectedProvider.description}</Text>
                      <Text className="kb-content-tertiary text-sm">
                        <a
                          href={selectedProvider.documentationLink}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="kb-content-info ml-2 hover:text-blue-800 underline"
                        >
                          Learn more in our documentation →
                        </a>
                      </Text>
                    </div>
                  </div>
                  {selectedProvider.credentialFields.map((field, index) => (
                    <div key={field.name} className="mb-4">
                      {field.type === 'textarea' ? (
                        <div className="space-y-2">
                          <TextField.Label>{field.label}</TextField.Label>
                          <textarea
                            placeholder={field.placeholder}
                            name={`credentials.${index}`}
                            value={data.credentials[index] || ''}
                            onChange={(e) => handleCredentialChange(index, e.target.value)}
                            autoComplete="off"
                            data-form-type="other"
                            data-lpignore="true"
                            spellCheck="false"
                            required={field.required}
                            rows={6}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-vertical"
                          />
                          {errors[`credentials.${index}` as keyof typeof errors] && (
                            <TextField.Error>
                              {errors[`credentials.${index}` as keyof typeof errors]}
                            </TextField.Error>
                          )}
                        </div>
                      ) : (
                        <TextField.Root
                          placeholder={field.placeholder}
                          name={`credentials.${index}`}
                          value={data.credentials[index] || ''}
                          onChange={(e) => handleCredentialChange(index, e.target.value)}
                          type={field.type === 'password' ? 'password' : 'text'}
                          required={field.required}
                          autoComplete="new-password"
                          data-form-type="other"
                          data-lpignore="true"
                        >
                          <TextField.Label>{field.label}</TextField.Label>
                          {errors[`credentials.${index}` as keyof typeof errors] && (
                            <TextField.Error>
                              {errors[`credentials.${index}` as keyof typeof errors]}
                            </TextField.Error>
                          )}
                        </TextField.Root>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* General credentials error */}
            {errors.credentials && typeof errors.credentials === 'string' && (
              <div className="kb-content-negative text-sm">{errors.credentials}</div>
            )}
          </div>

          <Dialog.Footer className="flex justify-between">
            <Dialog.Close asChild disabled={processing}>
              <Button variant="secondary">Cancel</Button>
            </Dialog.Close>
            <Button
              type="submit"
              loading={processing}
              disabled={!data.type || !data.name || !selectedProvider || !workspaceId}
            >
              Connect provider
            </Button>
          </Dialog.Footer>
        </form>
      </Dialog.Content>
    </Dialog.Root>
  );
}
