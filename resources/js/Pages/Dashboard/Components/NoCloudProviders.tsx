import { AWSIcon } from "@/Components/Icons/aws.svg";
import { DigitalOceanIcon } from "@/Components/Icons/digital-ocean.svg";
import { GoogleCloudIcon } from "@/Components/Icons/google-cloud.svg";
import { HetznerIcon } from "@/Components/Icons/hetzner.svg";
import { LeaseWebIcon } from "@/Components/Icons/leaseweb.svg";
import { LinodeIcon } from "@/Components/Icons/linode.svg";
import { OVHIcon } from "@/Components/Icons/ovh.svg";
import { VultrIcon } from "@/Components/Icons/vultr.svg";
import { PlusIcon } from "@/Components/Icons/plus.svg";
import { Button } from "@kibamail/owly";
import { Heading } from "@kibamail/owly/heading";
import { Text } from "@kibamail/owly/text";
import type { CloudProviderType } from "@/types";

interface CloudProviderInfo {
    type: CloudProviderType;
    name: string;
    icon: React.ComponentType<{ className?: string }>;
    implemented: boolean;
}

const cloudProviders: CloudProviderInfo[] = [
    // Available providers first
    {
        type: 'hetzner',
        name: 'Hetzner Cloud',
        icon: HetznerIcon,
        implemented: true,
    },
    {
        type: 'digital_ocean',
        name: 'DigitalOcean',
        icon: DigitalOceanIcon,
        implemented: true,
    },
    // Coming soon providers
    {
        type: 'aws',
        name: 'Amazon Web Services',
        icon: AWSIcon,
        implemented: false,
    },
    {
        type: 'google_cloud',
        name: 'Google Cloud Platform',
        icon: GoogleCloudIcon,
        implemented: false,
    },
    {
        type: 'leaseweb',
        name: 'LeaseWeb',
        icon: LeaseWebIcon,
        implemented: false,
    },
    {
        type: 'linode',
        name: 'Linode',
        icon: LinodeIcon,
        implemented: false,
    },
    {
        type: 'ovh',
        name: 'OVH',
        icon: OVHIcon,
        implemented: false,
    },
    {
        type: 'vultr',
        name: 'Vultr',
        icon: VultrIcon,
        implemented: false,
    },
];

export function NoCloudProviders() {
    const handleConnectProvider = (providerType: CloudProviderType) => {
        // TODO: Implement cloud provider connection flow
        console.log(`Connect ${providerType} provider`);
    };

    return (
        <div className="w-full h-full kb-background-hover flex flex-col items-center pt-24">
            <div className="flex flex-col items-center">
                <div className="w-24 h-24 rounded-lg flex items-center justify-center bg-white border kb-border-tertiary" />

                <div className="mt-4 flex flex-col items-center max-w-lg">
                    <Heading size="md" className="font-bold">
                        Connect a cloud provider
                    </Heading>

                    <Text className="text-center kb-content-tertiary mt-4">
                        You have not connected any cloud providers to this
                        workspace yet. Once you do, you'll be able to provision
                        your first cluster. You may connect multiple cloud
                        providers to a single workspace.
                    </Text>
                </div>

                <div className="w-full mt-6 flex flex-col gap-4 max-w-lg mx-auto">
                    {cloudProviders.map((provider) => {
                        const IconComponent = provider.icon;
                        return (
                            <div
                                key={provider.type}
                                className="w-full flex items-center justify-between rounded-md border kb-border-tertiary p-2.5 bg-white"
                            >
                                <div className="flex items-center gap-2">
                                    <IconComponent className="w-6 h-6" />
                                    <Text>{provider.name}</Text>
                                    {!provider.implemented && (
                                        <Text size='xs' className="text-xs px-2 py-0.5 lowercase kb-content-disabled font-bold kb-background-hover border kb-border-tertiary rounded-full">
                                            Coming Soon
                                        </Text>
                                    )}
                                </div>

                                <Button
                                    size="sm"
                                    className="pr-1"
                                    variant="secondary"
                                    onClick={() => handleConnectProvider(provider.type)}
                                    disabled={!provider.implemented}
                                >
                                    <PlusIcon />
                                    Connect provider
                                </Button>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
