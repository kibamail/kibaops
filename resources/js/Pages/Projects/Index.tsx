import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps, Project, Workspace } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Index({
  auth,
  workspace,
  projects,
}: PageProps<{ workspace: Workspace; projects: Project[] }>) {
  return (
    <AuthenticatedLayout
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>}
    >
      <Head title="Projects" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-semibold">Projects for {workspace.name}</h1>
                <Link
                  href={route('workspaces.projects.create', workspace.id)}
                  className="px-4 py-2 bg-gray-800 text-white rounded-md"
                >
                  Create Project
                </Link>
              </div>

              {projects.length === 0 ? (
                <p>No projects found.</p>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  {projects.map((project) => (
                    <div key={project.id} className="border rounded-lg p-4">
                      <h2 className="text-xl font-semibold mb-2">{project.name}</h2>
                      <div className="flex space-x-2 mt-4">
                        <Link
                          href={route('workspaces.projects.show', {
                            workspace: workspace.id,
                            project: project.id,
                          })}
                          className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm"
                        >
                          View
                        </Link>
                        <Link
                          href={route('workspaces.projects.edit', {
                            workspace: workspace.id,
                            project: project.id,
                          })}
                          className="px-3 py-1 bg-yellow-500 text-white rounded-md text-sm"
                        >
                          Edit
                        </Link>
                        <Link
                          href={route('workspaces.projects.destroy', {
                            workspace: workspace.id,
                            project: project.id,
                          })}
                          method="delete"
                          as="button"
                          className="px-3 py-1 bg-red-500 text-white rounded-md text-sm"
                        >
                          Delete
                        </Link>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
