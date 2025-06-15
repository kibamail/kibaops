import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps, Project } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Show({ project }: PageProps<{ project: Project }>) {
  return (
    <AuthenticatedLayout>
      <Head title={`Project: ${project.name}`} />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <div className="mb-6">
                <Link href={route('dashboard')} className="text-blue-500 hover:underline">
                  ← Back to Dashboard
                </Link>
              </div>

              <div className="mb-6">
                <h1 className="text-2xl font-semibold">{project.name}</h1>
              </div>

              <div className="mt-4">
                <p>
                  <strong>Slug:</strong> {project.slug}
                </p>
                <p>
                  <strong>Created:</strong> {new Date(project.created_at).toLocaleString()}
                </p>
                <p>
                  <strong>Updated:</strong> {new Date(project.updated_at).toLocaleString()}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
