import { BoxIcon } from '@/Components/Icons/box.svg';
import { Button } from '@kibamail/owly';
import { Heading } from '@kibamail/owly/heading';
import { Text } from '@kibamail/owly/text';

interface NoWorkspaceProjectsProps {
  onCreateProjectClick?: () => void;
}

export function NoWorkspaceProjects({ onCreateProjectClick }: NoWorkspaceProjectsProps) {
  return (
    <div className="w-full h-full kb-background-hover flex flex-col items-center pt-24">
      <div className="flex flex-col items-center">
        <div className="w-24 h-24 rounded-xl flex items-center justify-center bg-white border kb-border-tertiary">
          <BoxIcon className="w-18 h-18 kb-content-positive" />
        </div>

        <div className="mt-4 flex flex-col items-center max-w-lg">
          <Heading size="md" className="font-bold">
            Create your first project
          </Heading>

          <Text className="text-center kb-content-tertiary mt-4">
            You haven't created any projects in this workspace yet. Projects help you organize and
            manage your application environments, deployments, infrastructure and resources.
          </Text>
        </div>

        <div className="mt-6">
          <Button variant="primary" onClick={onCreateProjectClick}>
            Create new project
          </Button>
        </div>
      </div>
    </div>
  );
}
