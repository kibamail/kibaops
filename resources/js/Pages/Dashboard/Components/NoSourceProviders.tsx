import { BitbucketIcon } from '@/Components/Icons/bitbucket.svg';
import { CodeRepositoryIcon } from '@/Components/Icons/code-repository.svg';
import { GitHubIcon } from '@/Components/Icons/github.svg';
import { GitLabIcon } from '@/Components/Icons/gitlab.svg';
import { PlusIcon } from '@/Components/Icons/plus.svg';
import { Button } from '@kibamail/owly';
import { Heading } from '@kibamail/owly/heading';
import { Text } from '@kibamail/owly/text';

type SourceProviderType = 'github' | 'gitlab' | 'bitbucket';

interface SourceProviderInfo {
  type: SourceProviderType;
  name: string;
  implemented: boolean;
}

interface SourceProviderWithIcon extends SourceProviderInfo {
  icon: React.ComponentType<{ className?: string }>;
}

// Icon mapping for source providers
const providerIcons: Record<SourceProviderType, React.ComponentType<{ className?: string }>> = {
  github: GitHubIcon,
  gitlab: GitLabIcon,
  bitbucket: BitbucketIcon,
};

// Source provider data
const sourceProviders: SourceProviderInfo[] = [
  {
    type: 'github',
    name: 'GitHub',
    implemented: true,
  },
  {
    type: 'gitlab',
    name: 'GitLab',
    implemented: true,
  },
  {
    type: 'bitbucket',
    name: 'Bitbucket',
    implemented: true,
  },
];

export function NoSourceProviders() {

  const sourceProvidersWithIcons: SourceProviderWithIcon[] = sourceProviders
    .map((provider) => ({
      ...provider,
      icon: providerIcons[provider.type],
    }))
    .sort((a, b) => {
      if (a.implemented && !b.implemented) return -1;
      if (!a.implemented && b.implemented) return 1;
      return 0;
    });

  return (
    <div className="w-full h-full kb-background-hover flex flex-col items-center pt-24">
      <div className="flex flex-col items-center">
        <div className="w-24 h-24 rounded-xl flex items-center justify-center bg-white border kb-border-tertiary">
          <CodeRepositoryIcon className="w-18 h-18 kb-content-positive" />
        </div>

        <div className="mt-4 flex flex-col items-center max-w-lg">
          <Heading size="md" className="font-bold">
            Link a source code provider
          </Heading>

          <Text className="text-center kb-content-tertiary mt-4">
            You have not linked any source code providers to this workspace yet. Once you do, you'll
            be able to deploy your applications directly from your repositories. You may connect
            multiple source code providers to a single workspace.
          </Text>
        </div>

        <div className="w-full mt-6 flex flex-col gap-4 max-w-lg mx-auto">
          {sourceProvidersWithIcons.map((provider) => {
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
                    <Text
                      size="xs"
                      className="text-xs px-2 py-0.5 lowercase kb-content-disabled font-bold kb-background-hover border kb-border-tertiary rounded-full"
                    >
                      Coming Soon
                    </Text>
                  )}
                </div>

                {provider.implemented ? (
                  <a href={route('source-code.connect', { provider: provider.type, origin: window.location.href })}>
                    <Button
                      size="sm"
                      className="pr-1"
                      variant="secondary"
                    >
                      <PlusIcon />
                      Connect source
                    </Button>
                  </a>
                ) : (
                  <Button
                    size="sm"
                    className="pr-1"
                    variant="secondary"
                    disabled={true}
                  >
                    <PlusIcon />
                    Connect source
                  </Button>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
