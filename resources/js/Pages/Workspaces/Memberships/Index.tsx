import { Head } from '@inertiajs/react';
import { PageProps, Workspace, WorkspaceMembership } from '@/types';

interface Props extends PageProps {
  workspace: Workspace;
  memberships: WorkspaceMembership[];
}

export default function Index({ workspace, memberships }: Props) {
  return (
    <>
      <Head title={`${workspace.name} - Memberships`} />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <h1 className="text-2xl font-bold mb-4">
                Workspace Memberships: {workspace.name}
              </h1>
              <p>Memberships count: {memberships.length}</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
