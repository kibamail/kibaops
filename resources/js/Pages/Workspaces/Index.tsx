import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps, Workspace } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Index({ workspaces }: PageProps<{ workspaces: Workspace[] }>) {
  return (
    <AuthenticatedLayout
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Workspaces</h2>}
    >
      <Head title="Workspaces" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-medium">Your Workspaces</h3>
                <Link
                  href={route('workspaces.create')}
                  className="px-4 py-2 bg-gray-800 text-white rounded-md"
                >
                  Create Workspace
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
