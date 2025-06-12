import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

export default function Create(_props: PageProps) {
  return (
    <AuthenticatedLayout
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Workspace</h2>
      }
    >
      <Head title="Create Workspace" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <h3 className="text-lg font-medium mb-4">Create a New Workspace</h3>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
