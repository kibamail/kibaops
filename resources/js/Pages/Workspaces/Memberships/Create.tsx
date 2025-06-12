import { Head } from '@inertiajs/react';
import { PageProps, Workspace, Project } from '@/types';

interface Props extends PageProps {
  workspace: Workspace;
  projects: Project[];
}

export default function Create({ workspace, projects }: Props) {
  return (
    <>
      <Head title={`${workspace.name} - Create Membership`} />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <h1 className="text-2xl font-bold mb-4">
                Create Membership for: {workspace.name}
              </h1>
              <p>Available projects: {projects.length}</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
