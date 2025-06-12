import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps, Workspace } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Show({ workspace }: PageProps<{ workspace: Workspace }>) {
  return (
    <AuthenticatedLayout
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">Workspace Details</h2>
      }
    >
      <Head title={`Workspace: ${workspace.name}`} />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-medium">{workspace.name}</h3>
                <div className="space-x-2">
                  <Link
                    href={route('workspaces.edit', workspace.id)}
                    className="px-4 py-2 bg-gray-800 text-white rounded-md"
                  >
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
