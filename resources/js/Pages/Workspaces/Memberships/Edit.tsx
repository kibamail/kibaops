import { Head } from '@inertiajs/react';
import { PageProps, Workspace, WorkspaceMembership, Project } from '@/types';

interface Props extends PageProps {
  workspace: Workspace;
  membership: WorkspaceMembership;
  projects: Project[];
}

export default function Edit({ workspace, membership, projects }: Props) {
  return (
    <>
      <Head title={`${workspace.name} - Edit Membership`} />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <h1 className="text-2xl font-bold mb-4">
                Edit Membership for: {workspace.name}
              </h1>
              <p>Email: {membership.email}</p>
              <p>Available projects: {projects.length}</p>
              <p>Current project access: {membership.projects?.length || 0}</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
