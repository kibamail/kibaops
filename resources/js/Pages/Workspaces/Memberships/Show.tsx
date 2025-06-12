import { Head } from '@inertiajs/react';
import { PageProps, Workspace, WorkspaceMembership } from '@/types';

interface Props extends PageProps {
  workspace: Workspace;
  membership: WorkspaceMembership;
}

export default function Show({ workspace, membership }: Props) {
  return (
    <>
      <Head title={`${workspace.name} - Membership Details`} />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <h1 className="text-2xl font-bold mb-4">
                Membership Details for: {workspace.name}
              </h1>
              <p>Email: {membership.email}</p>
              <p>User ID: {membership.user_id || 'Pending'}</p>
              <p>Role: {membership.role}</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
